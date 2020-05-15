<?php declare(strict_types=1);

namespace WebGarden\Messaging\Stream;

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Redis;
use RedisException;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\SpecialIdentities;
use WebGarden\Messaging\Redis\Stream;

class Reader implements SpecialIdentities
{
    public const ITEM_RECEIVED = 'reader.item_received';
    public const TIMEOUT_REACHED = 'reader.timeout_reached';

    /** @var Stream[] List of stream objects indexed by their names */
    protected array $streams;

    protected Redis $redis;

    protected EventEmitter $emitter;

    protected ?Consumer $consumer = null;

    public function __construct(Redis $redis, array $streams, ?EventEmitterInterface $emitter = null)
    {
        $this->redis = $redis;
        $this->streams = array_combine(array_map('strval', $streams), $streams);
        $this->emitter = $emitter ?: new EventEmitter();
    }

    public function as(Consumer $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function followFrom(string $id = self::PENDING_MESSAGES, int $timeout = 0): void
    {
        if (in_array($id, [self::NEW_MESSAGES, self::NEW_GROUP_MESSAGES])) {
            $this->followNewEntries($timeout);
        } else {
            $this->follow($id, $timeout);
        }
    }

    public function followNewEntries(int $timeout = 0): void
    {
        $this->follow($this->determineSpecialId(), $timeout);
    }

    public function on($event, callable $listener): self
    {
        $this->emitter->on($event, $listener);

        return $this;
    }

    protected function follow(string $from, int $timeout = 0): void
    {
        $names = array_keys($this->streams);
        $streams = array_fill_keys($names, $from ?: $this->determineSpecialId());
        $currentTime = fn () => hrtime(true);

        while (true) {
            $start = $currentTime();

            try {
                $entries = $this->read($streams, $timeout);
            } catch (RedisException $exception) {
                $this->emitter->emit(self::TIMEOUT_REACHED, [$currentTime() - $start]);
                continue;
            }

            if (empty($entries)) {
                $streams = array_fill_keys($names, $this->determineSpecialId());
                continue;
            }

            foreach ($entries as $key => $streamEntries) {
                $streams[$key] = array_key_last($streamEntries);

                foreach ($streamEntries as $id => $values) {
                    $entry = new Entry($id, $values);
                    $this->emitter->emit(self::ITEM_RECEIVED, [
                        $entry,
                        $this->streams[$key],
                        $this->acknowledgeCallback($this->streams[$key], $entry),
                    ]);
                }
            }
        }
    }

    private function acknowledgeCallback(Stream $stream, Entry $entry): callable
    {
        if (!$this->onBehalfOfConsumer()) {
            return fn () => false;
        }

        return function () use ($stream, $entry): bool {
            return (bool) $this->redis->xAck(
                $stream->name(),
                $this->consumer->group()->name(),
                [$entry->id()]
            );
        };
    }

    private function onBehalfOfConsumer(): bool
    {
        return $this->consumer !== null;
    }

    private function determineSpecialId(): string
    {
        return $this->onBehalfOfConsumer() ? self::NEW_GROUP_MESSAGES : self::NEW_MESSAGES;
    }

    private function read(array $streams, $timeout, int $count = 0): array
    {
        if (!$this->onBehalfOfConsumer()) {
            return $this->redis->xRead($streams, $count, $timeout);
        }

        return $this->redis->xReadGroup(
            $this->consumer->group()->name(),
            $this->consumer->name(),
            $streams,
            $count,
            $timeout
        ) ?: [];
    }
}

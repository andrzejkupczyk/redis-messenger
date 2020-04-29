<?php

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

    protected EventEmitter $dispatcher;

    protected ?Consumer $consumer = null;

    public function __construct(Redis $redis, array $streams, ?EventEmitterInterface $dispatcher = null)
    {
        $this->redis = $redis;
        $this->streams = array_combine(array_map('strval', $streams), $streams);
        $this->dispatcher = $dispatcher ?: new EventEmitter();
    }

    public function as(Consumer $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function followFrom(string $id = self::PENDING_MESSAGES, int $timeout = 0)
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
        $this->dispatcher->on($event, $listener);

        return $this;
    }

    protected function follow(string $from, int $timeout = 0): void
    {
        $names = array_keys($this->streams);
        $streams = array_fill_keys($names, $from ?: $this->determineSpecialId());
        $currentTime = fn() => hrtime(true);

        while (true) {
            $start = $currentTime();

            try {
                $entries = $this->read($streams, $timeout);
            } catch (RedisException $exception) {
                $this->dispatcher->emit(self::TIMEOUT_REACHED, [$currentTime() - $start]);
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
                    $this->dispatcher->emit(self::ITEM_RECEIVED, [
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
            return fn() => false;
        }

        return function () use ($stream, $entry): bool {
            return (bool) $this->redis->xAck($stream, $this->consumer->group(), [$entry->id()]);
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

    private function read(array $streams, $timeout, ?int $count = null): array
    {
        if (!$this->onBehalfOfConsumer()) {
            return $this->redis->xRead($streams, $count, $timeout);
        }

        return $this->redis->xReadGroup(
            $this->consumer->group(),
            $this->consumer->name(),
            $streams,
            $count,
            $timeout
        ) ?: [];
    }
}

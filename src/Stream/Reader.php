<?php declare(strict_types=1);

namespace WebGarden\Messaging\Stream;

use Psr\EventDispatcher\EventDispatcherInterface;
use Redis;
use RedisException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use WebGarden\Messaging\Events\ItemReceived;
use WebGarden\Messaging\Events\TimeoutReached;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\IdsRange;
use WebGarden\Messaging\Redis\SpecialIdentities;
use WebGarden\Messaging\Redis\Stream;

class Reader implements SpecialIdentities
{
    /** @var Stream[] List of stream objects indexed by their names */
    protected array $streams;

    protected Redis $redis;

    protected EventDispatcherInterface $dispatcher;

    protected ?Consumer $consumer = null;

    public function __construct(Redis $redis, array $streams, ?EventDispatcherInterface $dispatcher = null)
    {
        $this->redis = $redis;
        $this->streams = array_combine(array_map('strval', $streams), $streams);
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
    }

    public function as(Consumer $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    /**
     * @see https://redis.io/commands/xrange
     *
     * @param int|null $limit Use to reduce the number of entries reported
     */
    public function readRange(IdsRange $range, ?int $limit = null): array
    {
        $arguments = [array_key_first($this->streams), $range->from(), $range->to()];
        if ($limit !== null) {
            $arguments[] = $limit;
        }

        return $this->redis->xRange(...$arguments);
    }

    /**
     * @see https://redis.io/commands/xrevrange
     *
     * @param int|null $limit Use to reduce the number of entries reported
     */
    public function readReversedRange(IdsRange $range, ?int $limit = null): array
    {
        $arguments = [array_key_first($this->streams), $range->to(), $range->from()];
        if ($limit !== null) {
            $arguments[] = $limit;
        }

        return $this->redis->xRevRange(...$arguments);
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

    public function on(string $event, callable $listener): self
    {
        $this->dispatcher->addListener($event, $listener);

        return $this;
    }

    protected function follow(string $from, int $timeout = 0): void
    {
        $names = array_keys($this->streams);
        $streams = array_fill_keys($names, $from);
        $currentTime = fn () => hrtime(true);
        $finiteLoop = defined('TEST_MODE') && TEST_MODE;

        do {
            $start = $currentTime();

            try {
                $entries = $this->read($streams, $timeout);
            } catch (RedisException $exception) {
                $this->dispatcher->dispatch(
                    new TimeoutReached($currentTime() - $start),
                    TimeoutReached::NAME
                );
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
                    $stream = $this->streams[$key];
                    $acknowledge = $this->acknowledgeCallback($stream, $entry);

                    $this->dispatcher->dispatch(
                        new ItemReceived($entry, $stream, $acknowledge),
                        ItemReceived::NAME
                    );
                }
            }
        } while (!$finiteLoop);
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

<?php

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;
use WebGarden\Messaging\Redis\Stream;
use WebGarden\Messaging\Stream\Reader;
use WebGarden\Messaging\Stream\Writer;

class Client
{
    protected Redis $redis;

    public static function connect(string $host)
    {
        $redis = new Redis();
        $redis->connect($host);

        return new static($redis);
    }

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function from(Stream ...$streams): Reader
    {
        return new Reader($this->redis, $streams);
    }

    public function to(Stream $stream): Writer
    {
        return new Writer($this->redis, $stream);
    }

    /**
     * Create a new consumer group associated with a stream.
     *
     * @return bool Whether or not the group has been created
     */
    public function createGroup(Group $group): bool
    {
        return $this->redis->xGroup('CREATE', $group->stream(), $group, $group->lastDeliveredId(), true);
    }

    /**
     * Remove a specific consumer from a consumer group.
     *
     * @return int The number of pending messages that the consumer had before it was deleted
     */
    public function removeConsumer(Consumer $consumer): int
    {
        $arguments = [$consumer->group()->stream(), $consumer->group(), $consumer->name()];

        return $this->redis->xGroup('DELCONSUMER', ...$arguments);
    }

    public function pending(Group $group, ?IdsRange $range = null, ?int $count = null): array
    {
        $range = $range ?: new IdsRange();

        return $this->redis->xPending(
            $group->stream(), $group, $range->from(), $range->to(), $count
        );
    }

    public function pendingFor(Consumer $consumer, ?IdsRange $range = null, ?int $count = null): array
    {
        $range = $range ?: new IdsRange();

        return $this->redis->xPending(
            $consumer->stream(), $consumer->group(), $range->from(), $range->to(), $count, $consumer
        );
    }
}

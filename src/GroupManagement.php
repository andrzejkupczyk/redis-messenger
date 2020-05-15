<?php declare(strict_types=1);

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;

trait GroupManagement
{
    protected Redis $redis;

    /**
     * Create a new consumer group associated with a stream.
     *
     * @return bool Whether or not the group has been created
     */
    public function createGroup(Group $group): bool
    {
        $arguments = [$group->stream()->name(), $group->name(), $group->lastDeliveredId(), true];

        return $this->redis->xGroup('CREATE', ...$arguments);
    }

    /**
     * Remove a specific consumer from a consumer group.
     *
     * @return int The number of pending messages that the consumer had before it was deleted
     */
    public function removeConsumer(Consumer $consumer): int
    {
        $arguments = [$consumer->stream()->name(), $consumer->group()->name(), $consumer->name()];

        return $this->redis->xGroup('DELCONSUMER', ...$arguments);
    }
}

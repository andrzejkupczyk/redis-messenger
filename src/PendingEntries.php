<?php declare(strict_types=1);

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;

trait PendingEntries
{
    protected Redis $redis;

    public function pendingFor(Group $group, ?IdsRange $range = null, int $limit = 1): array
    {
        $arguments = [$group->stream()->name(), $group->name()];

        if ($range) {
            array_push($arguments, $range->from(), $range->to(), $limit);
        }

        return $this->redis->xPending(...$arguments);
    }

    public function pendingOwnedBy(Consumer $consumer, IdsRange $range, int $limit = 1): array
    {
        return $this->redis->xPending(
            $consumer->stream()->name(),
            $consumer->group()->name(),
            $range->from(),
            $range->to(),
            $limit,
            $consumer->name(),
        );
    }
}

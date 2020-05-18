<?php declare(strict_types=1);

namespace WebGarden\Messaging\Stream;

use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;

/**
 * Supports Pending Entry Lists (PEL) management.
 */
class Claimer
{
    protected const DEFAULT_MIN_IDLE_TIME = 0;
    protected const DEFAULT_PENDING_TIME_LIMIT = 1;

    protected Redis $redis;

    protected Group $group;

    public function __construct(Redis $redis, Group $group)
    {
        $this->redis = $redis;
        $this->group = $group;
    }

    /**
     * Change the ownership of a pending messages.
     *
     * @param string[] $ids
     * @param Consumer|string $newOwner
     */
    public function reassignMessages(array $ids, $newOwner, int $minIdleTime = self::DEFAULT_MIN_IDLE_TIME): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->redis->xClaim(
            $this->group->stream()->name(),
            $this->group->name(),
            (string) $newOwner,
            $minIdleTime,
            $ids
        );
    }

    /**
     * Inspect the list of pending messages.
     */
    public function pending(?IdsRange $range = null, int $limit = self::DEFAULT_PENDING_TIME_LIMIT): array
    {
        $arguments = [$this->group->stream()->name(), $this->group->name()];

        if ($range) {
            array_push($arguments, $range->from(), $range->to(), $limit);
        }

        return $this->redis->xPending(...$arguments);
    }

    /**
     * Inspect the list of pending messages owned by the given consumer.
     *
     * @param Consumer|string $consumer
     */
    public function pendingOwnedBy($consumer, IdsRange $range, int $limit = self::DEFAULT_PENDING_TIME_LIMIT): array
    {
        return $this->redis->xPending(
            $this->group->stream()->name(),
            $this->group->name(),
            $range->from(),
            $range->to(),
            $limit,
            (string) $consumer,
        );
    }
}

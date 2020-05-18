<?php declare(strict_types=1);

namespace WebGarden\Messaging\Stream;

use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;

class Claimer
{
    protected const DEFAULT_MIN_IDLE_TIME = 0;

    protected Redis $redis;

    protected Group $group;

    protected array $ids = [];

    protected int $minIdleTime = self::DEFAULT_MIN_IDLE_TIME;

    public function __construct(Redis $redis, Group $group)
    {
        $this->redis = $redis;
        $this->group = $group;
    }

    /**
     * @param Consumer|string $consumer
     */
    public function assignTo($consumer): array
    {
        if (empty($this->ids)) {
            return [];
        }

        return $this->redis->xClaim(
            $this->group->stream()->name(),
            $this->group->name(),
            (string) $consumer,
            $this->minIdleTime,
            $this->ids
        );
    }

    public function messages(array $ids, int $minIdleTime = self::DEFAULT_MIN_IDLE_TIME): self
    {
        $this->ids = $ids;
        $this->minIdleTime = $minIdleTime;

        return $this;
    }
}

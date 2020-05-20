<?php declare(strict_types=1);

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

trait StreamManagement
{
    protected Redis $redis;

    public function generalInformation(Stream $stream): array
    {
        return $this->redis->xInfo('STREAM', $stream->name());
    }

    public function fullInformation(Stream $stream, int $count = 10): array
    {
        $arguments = ['STREAM', $stream->name(), 'FULL', 'COUNT', $count];

        return $this->redis->rawCommand('XINFO', ...$arguments);
    }

    public function consumers(Group $group): array
    {
        return $this->redis->xInfo('CONSUMERS', $group->stream()->name(), $group->name());
    }

    public function groups(Stream $stream): array
    {
        return $this->redis->xInfo('GROUPS', $stream->name());
    }

    /**
     * @see https://redis.io/commands/xlen
     */
    public function numberOfEntries(Stream $stream): int
    {
        return $this->redis->xLen($stream->name());
    }

    /**
     * @see https://redis.io/commands/xtrim
     *
     * @return int The number of entries deleted from the stream
     */
    public function trimStream(Stream $stream, int $maxLength): int
    {
        return $this->redis->xTrim($stream->name(), $maxLength, true);
    }
}

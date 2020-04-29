<?php

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;
use WebGarden\Messaging\Stream\Reader;
use WebGarden\Messaging\Stream\Writer;

class Client
{
    private Redis $redis;

    public static function connect(string $host)
    {
        $redis = new Redis();
        $redis->connect($host);

        return new self($redis);
    }

    protected function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function createGroup(Group $group): bool
    {
        return $this->redis->xGroup('CREATE', $group->stream(), $group, $group->lastDeliveredId(), true);
    }

    public function from(Stream ...$streams): Reader
    {
        return new Reader($this->redis, $streams);
    }

    public function to(Stream $stream): Writer
    {
        return new Writer($this->redis, $stream);
    }
}

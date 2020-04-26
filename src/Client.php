<?php

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Stream;

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

    public function to(Stream $stream): Writer
    {
        return new Writer($this->redis, $stream);
    }

    public function from(Stream ...$streams): Reader
    {
        return new Reader($this->redis, $streams);
    }
}

<?php declare(strict_types=1);

namespace WebGarden\Messaging;

use Redis;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;
use WebGarden\Messaging\Stream\Claimer;
use WebGarden\Messaging\Stream\Reader;
use WebGarden\Messaging\Stream\Writer;

class Client
{
    use GroupManagement;
    use StreamManagement;

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

    public function for(Group $group): Claimer
    {
        return new Claimer($this->redis, $group);
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

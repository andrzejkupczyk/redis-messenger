<?php declare(strict_types=1);

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
    use GroupManagement;

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

    public function pending(Group $group, ?IdsRange $range = null, ?int $count = null): array
    {
        $range = $range ?: new IdsRange();

        return $this->redis->xPending($group->stream(), $group, $range->from(), $range->to(), $count);
    }

    public function pendingFor(Consumer $consumer, ?IdsRange $range = null, ?int $count = null): array
    {
        $range = $range ?: new IdsRange();

        return $this->redis->xPending(
            $consumer->stream(),
            $consumer->group(),
            $range->from(),
            $range->to(),
            $count,
            $consumer
        );
    }
}

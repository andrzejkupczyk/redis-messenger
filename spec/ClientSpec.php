<?php

namespace spec\WebGarden\Messaging;

use PhpSpec\ObjectBehavior;
use Redis;
use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

class ClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new Redis());

        $this->shouldHaveType(Client::class);
    }

    function it_is_initializable_using_redis_config()
    {
        $this->beConstructedThrough('connect', ['hostname']);

        $this->shouldHaveType(Client::class);
    }

    function it_creates_consumer_group(Redis $redis)
    {
        $stream = new Stream('stream');
        $group = new Group('group', $stream);
        $redis->xGroup('CREATE', $stream, $group, '$', true)->willReturn(true);
        $this->beConstructedWith($redis);

        $result = $this->createGroup($group);

        $result->shouldBe(true);
    }

    function it_removes_consumer_from_a_group(Redis $redis)
    {
        $stream = new Stream('stream');
        $group = new Group('group', $stream);
        $consumer = new Consumer($group, 'consumer');
        $redis->xGroup('DELCONSUMER', $stream, $group, 'consumer')->willReturn(0);
        $this->beConstructedWith($redis);

        $result = $this->removeConsumer($consumer);

        $result->shouldBe(0);
    }
}

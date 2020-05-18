<?php

namespace spec\WebGarden\Messaging;

use PhpSpec\ObjectBehavior;
use Redis;
use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;
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

    function it_returns_pending_messages_overview(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xPending('stream', 'group')->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->pendingFor($group);

        $result->shouldBeArray();
    }

    function it_ignores_limit_if_range_is_missing(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xPending('stream', 'group')->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->pendingFor($group, null, 10);

        $result->shouldBeArray();
    }

    function it_returns_all_messages(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $range = IdsRange::fromDefaults();
        $amount = 10;
        $redis->xPending('stream', 'group', '-', '+', 10)->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->pendingFor($group, $range, $amount);

        $result->shouldBeArray();
    }

    function it_returns_pending_messages_owned_by_specified_consumer(Redis $redis)
    {
        $consumer = Consumer::fromNative('stream', 'group', 'name');
        $range = IdsRange::fromDefaults();
        $amount = 10;
        $redis->xPending('stream', 'group', '-', '+', 10, 'name')->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->pendingOwnedBy($consumer, $range, $amount);

        $result->shouldBeArray();
    }
}

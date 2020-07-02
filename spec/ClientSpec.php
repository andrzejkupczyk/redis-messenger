<?php

namespace spec\WebGarden\Messaging;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Redis;
use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Entry;
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
        $redis->xGroup(Argument::cetera())->willReturn(true);
        $this->beConstructedWith($redis);

        $result = $this->createGroup($group);

        $redis->xGroup('CREATE', 'stream', 'group', '$', true)->shouldHaveBeenCalled();
        $result->shouldBe(true);
    }

    function it_removes_consumer_from_a_group(Redis $redis)
    {
        $stream = new Stream('stream');
        $group = new Group('group', $stream);
        $consumer = new Consumer($group, 'consumer');
        $redis->xGroup(Argument::cetera())->willReturn(0);
        $this->beConstructedWith($redis);

        $result = $this->removeConsumer($consumer);

        $redis->xGroup('DELCONSUMER', 'stream', 'group', 'consumer')->shouldHaveBeenCalled();
        $result->shouldBe(0);
    }

    function it_returns_general_information_about_the_stream(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xInfo(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->generalInformation($stream);

        $redis->xInfo('STREAM', 'name')->shouldHaveBeenCalled();
        $result->shouldBeArray();
    }

    function it_returns_entire_state_of_the_stream(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xInfo(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->fullInformation($stream, 0);

        $redis->xInfo('STREAM', 'name', 'FULL', 0)->shouldHaveBeenCalled();
        $result->shouldBeArray();
    }

    function it_returns_all_consumer_groups_associated_with_the_stream(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xInfo(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->groups($stream);

        $redis->xInfo('GROUPS', 'name')->shouldHaveBeenCalled();
        $result->shouldBeArray();
    }

    function it_returns_list_of_consumers_in_a_specific_group(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xInfo(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis);

        $result = $this->consumers($group);

        $redis->xInfo('CONSUMERS', 'stream', 'group')->shouldHaveBeenCalled();
        $result->shouldBeArray();
    }

    function it_returns_the_number_of_entries_inside_a_stream(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xLen(Argument::cetera())->willReturn(0);
        $this->beConstructedWith($redis);

        $result = $this->numberOfEntries($stream);

        $redis->xLen('name')->shouldHaveBeenCalled();
        $result->shouldBeInt();
    }

    function it_removes_the_specified_entries_from_a_stream(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xDel(Argument::cetera())->willReturn(1);
        $this->beConstructedWith($redis);

        $result = $this->removeEntries($stream, new Entry('0-1', []), new Entry('0-2', []));

        $redis->xDel('name', ['0-1', '0-2'])->shouldHaveBeenCalled();
        $result->shouldBeInt();
    }

    function it_trims_the_stream_to_the_approximate_number_of_items(Redis $redis)
    {
        $stream = new Stream('name');
        $redis->xTrim(Argument::cetera())->willReturn(0);
        $this->beConstructedWith($redis);

        $result = $this->trimStream($stream, 10);

        $redis->xTrim('name', 10, true)->shouldHaveBeenCalled();
        $result->shouldBeInt();
    }
}

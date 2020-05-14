<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

class ConsumerSpec extends ObjectBehavior
{
    function it_is_initializable(Group $group)
    {
        $this->beConstructedWith($group, 'consumer_name');

        $this->shouldHaveType(Consumer::class);
    }

    function it_is_initializable_using_native_values()
    {
        $this->beConstructedThrough('fromNative', ['stream_name', 'group_name', 'consumer_name']);

        $this->shouldHaveType(Consumer::class);
    }

    function it_gives_itself_a_default_name_when_none_provided(Group $group)
    {
        $group->__toString()->willReturn('group_name');

        $this->beConstructedWith($group);

        $this->name()->shouldBe('group_name_client');
    }

    function it_returns_the_stream_with_which_the_group_is_associated()
    {
        $stream = new Stream('stream');

        $this->beConstructedWith(new Group('group', $stream));

        $this->stream()->shouldBe($stream);
    }

    function it_has_a_string_representation(Group $group)
    {
        $this->beConstructedWith($group, 'consumer_name');

        $this->__toString()->shouldBe('consumer_name');
    }
}

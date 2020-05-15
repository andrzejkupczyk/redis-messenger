<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

class GroupSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('group_name', new Stream('stream'));

        $this->shouldHaveType(Group::class);
    }

    function it_is_initializable_using_native_values()
    {
        $this->beConstructedThrough('fromNative', ['group_name', 'stream_name']);

        $this->shouldHaveType(Group::class);
    }

    function it_shows_only_new_elements_to_consumers_by_default()
    {
        $this->beConstructedWith('group_name', new Stream('stream'));

        $this->lastDeliveredId()->shouldBe('$');
    }

    function it_has_a_string_representation()
    {
        $this->beConstructedWith('group_name', new Stream('stream'));

        $this->__toString()->shouldBe('group_name');
    }
}

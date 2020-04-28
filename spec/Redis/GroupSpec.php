<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

class GroupSpec extends ObjectBehavior
{
    function let(Stream $stream)
    {
        $this->beConstructedWith('group_name', $stream);
    }

    function it_is_initializable(Stream $stream)
    {
        $this->shouldHaveType(Group::class);
    }

    function it_shows_only_new_elements_to_consumers_by_default()
    {
        $this->lastDeliveredId()->shouldBe('$');
    }

    function it_has_a_string_representation()
    {
        $this->__toString()->shouldBe('group_name');
    }
}

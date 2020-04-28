<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\Stream;

class StreamSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('stream_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Stream::class);
    }

    function it_has_a_string_representation()
    {
        $this->__toString()->shouldBe('stream_name');
    }
}

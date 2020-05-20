<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\IdsRange;
use WebGarden\Messaging\Redis\Stream;

class IdsRangeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('1526985054069', '1526985055069');

        $this->shouldHaveType(IdsRange::class);
    }

    function it_can_be_initialized_for_every_entry()
    {
        $this->beConstructedThrough('forEveryEntry');

        $this->shouldHaveType(IdsRange::class);
        $this->from()->shouldBe(IdsRange::MIN_ID_POSSIBLE);
        $this->to()->shouldBe(IdsRange::MAX_ID_POSSIBLE);
    }

    function it_can_be_initialized_for_a_single_entry()
    {
        $this->beConstructedThrough('forSingleEntry', ['1526984818136-0']);

        $this->shouldHaveType(IdsRange::class);
        $this->from()->shouldBeEqualTo($this->to());
    }
}

<?php

namespace spec\WebGarden\Messaging\Redis;

use PhpSpec\ObjectBehavior;
use WebGarden\Messaging\Redis\Entry;

class EntrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('0-0', ['field' => 'value']);

        $this->shouldHaveType(Entry::class);
    }

    function it_can_be_composed_using_auto_generated_id()
    {
        $this->beConstructedThrough('compose', [['field' => 'value']]);

        $this->id()->shouldBe('*');
    }
}

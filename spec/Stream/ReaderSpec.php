<?php

namespace spec\WebGarden\Messaging\Stream;

use Evenement\EventEmitter;
use PhpSpec\ObjectBehavior;
use Redis;
use WebGarden\Messaging\Redis\Stream;
use WebGarden\Messaging\Stream\Reader;

class ReaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new Redis(), [new Stream('stream')], new EventEmitter());

        $this->shouldHaveType(Reader::class);
    }
}

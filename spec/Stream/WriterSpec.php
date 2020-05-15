<?php

namespace spec\WebGarden\Messaging\Stream;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument as Arg;
use Redis;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;
use WebGarden\Messaging\Stream\Writer;

class WriterSpec extends ObjectBehavior
{
    function let(Redis $redis)
    {
        $stream = new Stream('stream');
        $redis->xAdd($stream->name(), Arg::type('string'), Arg::type('array'))->willReturnArgument(1);

        $this->beConstructedWith($redis, $stream);
    }

    function it_is_initializable(Redis $redis, Stream $stream)
    {
        $this->shouldHaveType(Writer::class);
    }

    function it_returns_id_of_the_last_added_entry(Redis $redis)
    {
        $entry1 = new Entry('0-0', ['field' => 'value']);
        $entry2 = new Entry('1-0', ['field' => 'value']);
        $entry3 = new Entry('2-0', ['field' => 'value']);

        $this->add($entry1, $entry2, $entry3)->shouldBe('2-0');
        $redis->xAdd(Arg::any(), Arg::any(), Arg::any())->shouldHaveBeenCalledTimes(3);
    }
}

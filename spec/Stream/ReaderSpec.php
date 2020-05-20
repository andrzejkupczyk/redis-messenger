<?php

namespace spec\WebGarden\Messaging\Stream;

use Evenement\EventEmitter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Redis;
use WebGarden\Messaging\Redis\{Consumer, Entry, IdsRange, Stream};
use WebGarden\Messaging\Stream\Reader;

class ReaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new Redis(), [new Stream('stream')]);

        $this->shouldHaveType(Reader::class);
    }

    function it_reads_messages_starting_from_the_given_message_id(Redis $redis)
    {
        $redis->xRead(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('stream_1'), new Stream('stream_2')]);

        $this->followFrom('0-0', 10);

        $redis->xRead(['stream_1' => '0-0', 'stream_2' => '0-0'], 0, 10)->shouldHaveBeenCalled();
    }

    function it_reads_on_behalf_of_a_consumer(Redis $redis)
    {
        $consumer = Consumer::fromNative('stream', 'group', 'consumer');
        $redis->xReadGroup(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('stream')]);

        $this->as($consumer)->followFrom('0-0');

        $redis->xReadGroup('group', 'consumer', ['stream' => '0-0'], 0, 0)->shouldHaveBeenCalled();
    }

    function it_handles_incomplete_ids(Redis $redis)
    {
        $redis->xRead(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('stream')]);

        $this->followFrom('0');

        $redis->xRead(['stream' => '0'], 0, 0)->shouldHaveBeenCalled();
    }

    function it_handles_special_ids(Redis $redis)
    {
        $redis->xRead(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('stream')]);

        $this->followFrom('$');

        $redis->xRead(['stream' => '$'], 0, 0)->shouldHaveBeenCalled();
    }

    function it_can_determine_special_id_based_on_consumer_presence(Redis $redis)
    {
        $redis->xRead(Argument::cetera())->willReturn([]);
        $redis->xReadGroup(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('stream')]);
        $consumer = Consumer::fromNative('stream', 'group', 'consumer');

        $this->followNewEntries();
        $redis->xRead(['stream' => '$'], 0, 0)->shouldHaveBeenCalled();

        $this->as($consumer)->followNewEntries();
        $redis->xReadGroup('group', 'consumer', ['stream' => '>'], 0, 0)->shouldHaveBeenCalled();
    }

    function it_emits_an_event_when_redis_exception_is_thrown(Redis $redis, EventEmitter $emitter)
    {
        $redis->xRead(Argument::cetera())->willThrow('RedisException');
        $this->beConstructedWith($redis, [new Stream('stream')], $emitter);

        $this->followFrom('0-0');

        $emitter->emit('reader.timeout_reached', Argument::type('array'))->shouldHaveBeenCalled();
    }

    function it_emits_an_event_when_receives_an_item_from_the_stream(Redis $redis, EventEmitter $emitter)
    {
        $redisResponse = ['stream' => ['0-0' => ['foo' => 'bar'], '0-1' => ['baz' => 'qux']]];
        $redis->xRead(Argument::cetera())->willReturn($redisResponse);
        $this->beConstructedWith($redis, [new Stream('stream')], $emitter);

        $this->followFrom('0-0');

        $emitter->emit('reader.item_received', Argument::that(function ($argument) {
            return count($argument) === 3
                && $argument[0] instanceof Entry
                && $argument[1] instanceof Stream
                && is_callable($argument[2]);
        }))->shouldHaveBeenCalledTimes(2);
    }

    function it_reads_stream_entries_matching_given_range_of_ids(Redis $redis)
    {
        $redis->xRange(Argument::cetera())->willReturn([]);
        $this->beConstructedWith($redis, [new Stream('first_stream'), new Stream('second_stream')]);

        $result = $this->readRange(new IdsRange('-', '+'), 2);

        $result->shouldBeArray();
        $redis->xRange('first_stream', '-', '+', 2)->shouldHaveBeenCalled();
        $redis->xRange('second_stream', '-', '+', 2)->shouldNotHaveBeenCalled();
    }
}

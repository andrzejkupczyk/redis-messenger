<?php

namespace spec\WebGarden\Messaging\Stream;

use PhpSpec\ObjectBehavior;
use Redis;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;
use WebGarden\Messaging\Stream\Claimer;

class ClaimerSpec extends ObjectBehavior
{
    function it_is_initializable(Redis $redis, Group $group)
    {
        $this->beConstructedWith($redis, $group);

        $this->shouldHaveType(Claimer::class);
    }

    function it_returns_pending_messages_overview(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xPending('stream', 'group')->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->pending();

        $result->shouldBeArray();
    }

    function it_ignores_limit_if_range_is_missing(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xPending('stream', 'group')->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->pending(null, 10);

        $result->shouldBeArray();
    }

    function it_returns_all_messages(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $range = IdsRange::forEveryEntry();
        $amount = 10;
        $redis->xPending('stream', 'group', '-', '+', 10)->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->pending($range, $amount);

        $result->shouldBeArray();
    }

    function it_returns_pending_messages_owned_by_specified_consumer(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $range = IdsRange::forEveryEntry();
        $amount = 10;
        $redis->xPending('stream', 'group', '-', '+', 10, 'consumer')->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->pendingOwnedBy('consumer', $range, $amount);

        $result->shouldBeArray();
    }


    function it_changes_ownership_of_the_specified_message_ids(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xClaim('stream', 'group', 'consumer', 10, ['0-0'])->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->reassignMessages(['0-0'], 'consumer', 10);

        $result->shouldBeArray();
    }

    function it_returns_empty_result_when_no_message_ids_specified(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $this->beConstructedWith($redis, $group);

        $result = $this->reassignMessages([], 'consumer');

        $redis->xClaim('stream', 'group', 'consumer', 0, [])->shouldNotHaveBeenCalled();
        $result->shouldBeEqualTo([]);
    }
}

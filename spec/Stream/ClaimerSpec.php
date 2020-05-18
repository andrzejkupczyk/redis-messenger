<?php

namespace spec\WebGarden\Messaging\Stream;

use PhpSpec\ObjectBehavior;
use Redis;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Stream\Claimer;

class ClaimerSpec extends ObjectBehavior
{
    function it_is_initializable(Redis $redis, Group $group)
    {
        $this->beConstructedWith($redis, $group);

        $this->shouldHaveType(Claimer::class);
    }

    function it_changes_ownership_of_the_specified_message_ids(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $redis->xClaim('stream', 'group', 'consumer', 10, ['0-0'])->willReturn([]);
        $this->beConstructedWith($redis, $group);

        $result = $this->messages(['0-0'], 10)->assignTo('consumer');

        $result->shouldBeArray();
    }

    function it_returns_empty_result_when_no_message_ids_specified(Redis $redis)
    {
        $group = Group::fromNative('group', 'stream');
        $this->beConstructedWith($redis, $group);

        $result = $this->assignTo('consumer');

        $redis->xClaim('stream', 'group', 'consumer', 0, [])->shouldNotHaveBeenCalled();
        $result->shouldBeEqualTo([]);
    }
}

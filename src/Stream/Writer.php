<?php

namespace WebGarden\Messaging\Stream;

use Redis;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

class Writer
{
    protected Redis $redis;

    protected Stream $stream;

    public function __construct(Redis $redis, Stream $stream)
    {
        $this->redis = $redis;
        $this->stream = $stream;
    }

    public function add(Entry ...$entries): string
    {
        $lastEntryId = null;

        foreach ($entries as $entry) {
            $lastEntryId = $this->redis->xAdd($this->stream, $entry->id(), $entry->values());
        }

        return $lastEntryId;
    }
}

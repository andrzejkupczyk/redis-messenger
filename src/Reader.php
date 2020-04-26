<?php

namespace WebGarden\Messaging;

use Redis;
use RedisException;
use WebGarden\Messaging\Redis\Stream;

class Reader
{
    public const NEW_ENTRIES = '$';
    public const PENDING_ENTRIES = '0';

    protected Redis $redis;

    /** @var Stream[] */
    protected array $streams;

    public function __construct(Redis $redis, array $streams)
    {
        $this->redis = $redis;
        $this->streams = $streams;
    }

    public function follow(?string $from = null)
    {
        $streamNames = array_map('strval', $this->streams);
        $streams = array_fill_keys($streamNames, $from ?: self::NEW_ENTRIES);

        while (true) {
            try {
                $data = $this->redis->xRead($streams, null, 1);

                if (empty($data)) {
                    $streams = array_fill_keys($streamNames, self::NEW_ENTRIES);
                    continue;
                }

                foreach ($data as $stream => $entries) {
                    $streams[$stream] = array_key_last($entries);

                    foreach ($entries as $id => $values) {
                        echo "Received item {$id} from {$stream} " . json_encode($values) . PHP_EOL;
                    }
                }
            } catch (RedisException $exception) {
                echo 'Disconnected' . PHP_EOL;
            }
        }
    }
}

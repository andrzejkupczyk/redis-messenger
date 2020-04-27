<?php

namespace WebGarden\Messaging;

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Redis;
use RedisException;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

class Reader
{
    public const TIMEOUT_REACHED = 'reader.timeout_reached';
    public const ITEM_RECEIVED = 'reader.item_received';
    public const NEW_ENTRIES = '$';
    public const PENDING_ENTRIES = '0';

    protected Redis $redis;

    /** @var Stream[] List of stream objects indexed by their names */
    protected array $streams;

    private EventEmitter $dispatcher;

    public function __construct(Redis $redis, array $streams, ?EventEmitterInterface $dispatcher = null)
    {
        $this->redis = $redis;
        $this->streams = array_combine(array_map('strval', $streams), $streams);
        $this->dispatcher = $dispatcher ?: new EventEmitter();
    }

    public function await(int $timeout = 0)
    {
        $names = array_keys($this->streams);
        $streams = array_fill_keys($names, self::NEW_ENTRIES);
        $currentTime = fn() => hrtime(true);

        while (true) {
            $start = $currentTime();

            try {
                $data = $this->redis->xRead($streams, null, $timeout);
            } catch (RedisException $exception) {
                $this->dispatcher->emit(self::TIMEOUT_REACHED, [($currentTime() - $start) / 1e+9]);
                continue;
            }

            if (empty($data)) {
                $streams = array_fill_keys($names, self::NEW_ENTRIES);
                continue;
            }

            foreach ($data as $key => $entries) {
                $streams[$key] = array_key_last($entries);

                foreach ($entries as $id => $values) {
                    $this->dispatcher->emit(self::ITEM_RECEIVED, [
                        new Entry($id, $values),
                        $this->streams[$key],
                    ]);
                }
            }
        }
    }

    public function on($event, callable $listener): self
    {
        $this->dispatcher->on($event, $listener);

        return $this;
    }
}

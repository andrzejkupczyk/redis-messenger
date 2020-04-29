<?php

require __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Stream\Reader;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

$streamNames = explode(',', $argv[1] ?? 'stream');
$from = $argv[2] ?? 0;

$client = Client::connect('redis');
$streams = array_map(function ($name) {
    return new Stream($name);
}, $streamNames);

$consumer = new Consumer(new Group('consumer_group', $streams[0]));

$client->createGroup($consumer->group());

$client
    ->from(...$streams)
    ->as($consumer)
    ->on(Reader::TIMEOUT_REACHED, function ($time) {
        printf("Idle running for %01.3f seconds\n", $time / 1e+9);
    })
    ->on(Reader::ITEM_RECEIVED, function (Entry $entry, Stream $stream, callable $acknowledge) {
        printf("Received item %s from %s\n", $entry->id(), $stream);
        $acknowledge();
    })
    ->followFrom($from);

<?php

require_once __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Reader;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

$streamNames = explode(',', $argv[1] ?? 'stream');
$from = $argv[2] ?? null;

$client = Client::connect('redis');
$streams = array_map(function ($name) {
    return new Stream($name);
}, $streamNames);

$client
    ->from(...$streams)
    ->on(Reader::TIMEOUT_REACHED, function ($seconds) {
        printf("Idle running for %01.3f seconds\n", $seconds);
    })
    ->on(Reader::ITEM_RECEIVED, function (Entry $entry, Stream $stream) {
        printf("Received item %s from %s\n", $entry->id(), $stream);
    })
    ->await();

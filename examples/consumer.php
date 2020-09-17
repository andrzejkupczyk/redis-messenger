<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Events\{ItemReceived, TimeoutReached};
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

$streamNames = explode(',', $argv[1] ?? 'stream');
$from = $argv[2] ?? '0';

$client = Client::connect('redis');
$streams = array_map(function ($name) {
    return new Stream($name);
}, $streamNames);

$consumer = new Consumer(new Group('consumer_group', $streams[0]));

$client->createGroup($consumer->group());

$client
    ->from(...$streams)
    ->as($consumer)
    ->on(TimeoutReached::NAME, function (TimeoutReached $event) {
        printf("Idle running for %01.3f seconds\n", $event->elapsedTime / 1e+9);
    })
    ->on(ItemReceived::NAME, function (ItemReceived $event) {
        printf("Received item %s from %s\n", $event->entry->id(), $event->stream);
        call_user_func($event->acknowledge);
    })
    ->followFrom($from);

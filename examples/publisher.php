<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

$streamName = $argv[1] ?? 'stream';
$number = $argv[2] ?? 1;

$client = Client::connect('redis');
$stream = new Stream($streamName);

$entries = [];
for ($i = 0; $i < $number; $i++) {
    $entries[] = Entry::compose(['iteration' => $i + 1]);
}

$lastEntryId = $client->to($stream)->add($entries);

printf("There are now a total of %d entries\n", $client->numberOfEntries($stream));

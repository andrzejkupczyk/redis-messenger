<?php declare(strict_types=1);

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\Stream;

require __DIR__ . '/../vendor/autoload.php';

$stream = new Stream($argv[1] ?? 'stream');
$group = new Group($argv[2] ?? 'consumer_group', $stream);

$client = Client::connect('redis');
$client->to($stream)->add(Entry::compose(['foo' => 'bar']));
$client->createGroup($group);

// 1. General information about the stream
var_dump($client->generalInformation($stream));

// 2. Entire state of the stream
var_dump($client->fullInformation($stream));

// 3. All the consumer groups associated with the stream
var_dump($client->groups($stream));

// 4. List of every consumer in a specific consumer group
var_dump($client->consumers($group));

// 5. Number of entries inside a stream
var_dump($client->numberOfEntries($stream));

// 6. Trimming the stream to a given number of items
var_dump($client->trimStream($stream, 10));

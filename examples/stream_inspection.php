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
print_r($client->generalInformation($stream));

// 2. Entire state of the stream
print_r($client->fullInformation($stream));

// 3. All the consumer groups associated with the stream
print_r($client->groups($stream));

// 4. List of every consumer in a specific consumer group
print_r($client->consumers($group));

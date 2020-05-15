<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

$streamName = $argv[1] ?? 'stream';
$number = $argv[2] ?? 1;

$client = Client::connect('redis');
$stream = new Stream($streamName);

for ($i = 0; $i < $number; $i++) {
    $client->to($stream)->add(Entry::compose(['iteration' => $i + 1]));
}

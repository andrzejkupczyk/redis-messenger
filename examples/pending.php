<?php declare(strict_types=1);

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Consumer;
use WebGarden\Messaging\Redis\Group;
use WebGarden\Messaging\Redis\IdsRange;

require __DIR__ . '/../vendor/autoload.php';

$client = Client::connect('redis');

$group = Group::fromNative($argv[2] ?? 'group', $argv[1] ?? 'stream');

// 1. Overview (the simple XPENDING form)
$result = $client->for($group)->pending();
var_dump($result);

// 2. All the pending messages (the extended XPENDING form)
$result = $client->for($group)->pending(IdsRange::fromDefaults(), 10);
var_dump($result);

// 3. Messages having a specific owner
$consumer = new Consumer($group);
$result = $client->for($group)->pendingOwnedBy($consumer, IdsRange::fromDefaults(), 10);
var_dump($result);

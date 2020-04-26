<?php

require_once __DIR__ . '/../vendor/autoload.php';

use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\Stream;

$streamNames = explode(',', $argv[1] ?? 'stream');
$from = $argv[2] ?? null;

$client = Client::connect('redis');
$streams = array_map(function ($name) {
    return new Stream($name);
}, $streamNames);

$client->from(...$streams)->follow($from);

# Redis Messenger
![PHP requirement](https://img.shields.io/badge/PHP-^7.4-blue.svg?logo=php&style=for-the-badge)
![Code quality](https://img.shields.io/scrutinizer/quality/g/andrzejkupczyk/redis-messenger?logo=scrutinizer&style=for-the-badge)

Framework-agnostic Redis Streams client.

## Examples of use

This package aims to support all [Redis Streams commands](https://redis.io/commands#stream) that are available. 
Examples listed below illustrate only the simplest use cases, but [more examples](https://github.com/andrzejkupczyk/redis-messenger/tree/master/examples) are provided.

### Publishing messages
```php
use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\{Entry,Stream};

$client = Client::connect('redis');

$client
    ->to(new Stream('mystream'))
    ->add(
        Entry::compose(['name' => 'Sara', 'surname' => 'OConnor']),
        Entry::compose(['field1' => 'value1', 'field2' => 'value2'])
    );
```

### Consuming messages
```php
use WebGarden\Messaging\Client;
use WebGarden\Messaging\Redis\{Entry,Stream};

$client = Client::connect('redis');

$client
    ->from(new Stream('mystream'))
    ->on('reader.item_received', function (Entry $entry) {
        printf("Received item %s\n", $entry->id());
    })
    ->followNewEntries();
```

## Install
Via Composer
```
composer require andrzejkupczyk/redis-messenger
```

ℹ️️ package requires the [PhpRedis](https://github.com/phpredis/phpredis) PHP extension

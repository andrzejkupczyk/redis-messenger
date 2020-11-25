<?php declare(strict_types=1);

namespace WebGarden\Messaging\Laravel;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use WebGarden\Messaging\Client;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected const CONNECTION_NAME = 'default';

    public function register(): void
    {
        $this->app->singleton(Client::class, fn () => new Client($this->connection()->client()));

        $this->app->alias(Client::class, 'RedisMessenger');
    }

    protected function connection(): Connection
    {
        return Redis::connection(static::CONNECTION_NAME);
    }
}

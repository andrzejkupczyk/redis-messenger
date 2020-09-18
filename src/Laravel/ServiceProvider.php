<?php declare(strict_types=1);

namespace WebGarden\Messaging\Laravel;

use WebGarden\Messaging\Client;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            /** @var \Illuminate\Redis\Connections\Connection $connection */
            $connection = $app['redis']->connection();

            return new Client($connection->client());
        });

        $this->app->alias(Client::class, 'RedisMessenger');
    }
}

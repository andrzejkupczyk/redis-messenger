<?php declare(strict_types=1);

namespace WebGarden\Messaging\Events;

use Symfony\Contracts\EventDispatcher\Event;

class TimeoutReached extends Event
{
    public const NAME = 'reader.timeout_reached';

    public int $elapsedTime;

    public function __construct(int $elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;
    }
}

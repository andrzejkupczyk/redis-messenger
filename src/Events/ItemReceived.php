<?php declare(strict_types=1);

namespace WebGarden\Messaging\Events;

use Symfony\Contracts\EventDispatcher\Event;
use WebGarden\Messaging\Redis\Entry;
use WebGarden\Messaging\Redis\Stream;

class ItemReceived extends Event
{
    public const NAME = 'reader.item_received';

    public Entry $entry;

    public Stream $stream;

    /** @var callable */
    public $acknowledge;

    public function __construct(Entry $entry, Stream $stream, callable $acknowledge)
    {
        $this->entry = $entry;
        $this->stream = $stream;
        $this->acknowledge = $acknowledge;
    }
}

<?php declare(strict_types=1);

namespace WebGarden\Messaging\Laravel;

use WebGarden\Messaging\Client;

/**
 * @method static \WebGarden\Messaging\Stream\Claimer for (\WebGarden\Messaging\Redis\Group $group)
 * @method static \WebGarden\Messaging\Stream\Reader from(\WebGarden\Messaging\Redis\Stream ...$streams)
 * @method static \WebGarden\Messaging\Stream\Writer to(\WebGarden\Messaging\Redis\Stream $stream)
 * @method static bool createGroup(\WebGarden\Messaging\Redis\Group $group)
 * @method static int removeConsumer(\WebGarden\Messaging\Redis\Consumer $consumer)
 * @method static array generalInformation(\WebGarden\Messaging\Redis\Stream $stream)
 * @method static array fullInformation(\WebGarden\Messaging\Redis\Stream $stream, int $count = 10)
 * @method static array consumers(\WebGarden\Messaging\Redis\Group $group)
 * @method static array groups(\WebGarden\Messaging\Redis\Stream $stream)
 * @method static int numberOfEntries(\WebGarden\Messaging\Redis\Stream $stream)
 * @method static int removeEntries(\WebGarden\Messaging\Redis\Stream $stream, \WebGarden\Messaging\Redis\Entry ...$entries)
 * @method static int trimStream(\WebGarden\Messaging\Redis\Stream $stream, int $maxLength)
 *
 * @see \WebGarden\Messaging\Client
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}

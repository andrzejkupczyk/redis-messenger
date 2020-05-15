<?php declare(strict_types=1);

namespace WebGarden\Messaging\Redis;

interface SpecialIdentities
{
    public const NEW_GROUP_MESSAGES = '>';
    public const NEW_MESSAGES = '$';
    public const PENDING_MESSAGES = '0';
    public const MIN_ID_POSSIBLE = '-';
    public const MAX_ID_POSSIBLE = '+';
}

<?php

namespace WebGarden\Messaging\Redis;

interface SpecialIdentities
{
    public const NEW_GROUP_MESSAGES = '>';
    public const NEW_MESSAGES = '$';
    public const PENDING_MESSAGES = '0';
}

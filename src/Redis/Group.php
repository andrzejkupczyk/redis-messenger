<?php declare(strict_types=1);

namespace WebGarden\Messaging\Redis;

class Group implements SpecialIdentities
{
    protected string $name;

    protected Stream $stream;

    protected string $lastDeliveredId;

    public function __construct(string $name, Stream $stream)
    {
        $this->name = $name;
        $this->stream = $stream;
        $this->lastDeliveredId = self::NEW_MESSAGES;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function stream(): Stream
    {
        return $this->stream;
    }

    public function lastDeliveredId(): string
    {
        return $this->lastDeliveredId;
    }

    public function __toString()
    {
        return $this->name;
    }
}

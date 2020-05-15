<?php declare(strict_types=1);

namespace WebGarden\Messaging\Redis;

class Stream
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name();
    }
}

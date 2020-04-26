<?php

namespace WebGarden\Messaging\Redis;

class Entry
{
    private const AUTO_GENERATED_ID = '*';

    protected string $id;

    protected array $values;

    public static function compose(array $values)
    {
        return new static(self::AUTO_GENERATED_ID, $values);
    }

    public function __construct(string $id, array $values)
    {
        $this->id = $id;
        $this->values = $values;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function values(): array
    {
        return $this->values;
    }
}

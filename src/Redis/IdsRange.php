<?php declare(strict_types=1);

namespace WebGarden\Messaging\Redis;

class IdsRange implements SpecialIdentities
{
    protected string $from;

    protected string $to;

    public static function fromDefaults()
    {
        return new self(self::MIN_ID_POSSIBLE, self::MAX_ID_POSSIBLE);
    }

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function from(): string
    {
        return $this->from;
    }

    public function to(): string
    {
        return $this->to;
    }
}

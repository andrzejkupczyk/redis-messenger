<?php declare(strict_types=1);

namespace WebGarden\Messaging\Redis;

class Consumer
{
    protected const DEFAULT_NAME = '%s_client';

    protected string $name;

    protected Group $group;

    private static function determineDefaultName(Group $group): string
    {
        return sprintf(static::DEFAULT_NAME, $group);
    }

    public static function fromNative(string $stream, string $group, ?string $name = null)
    {
        return new static(new Group($group, new Stream($stream)), $name);
    }

    public function __construct(Group $group, ?string $name = null)
    {
        $this->name = $name ?: self::determineDefaultName($group);
        $this->group = $group;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function group(): Group
    {
        return $this->group;
    }

    public function stream(): Stream
    {
        return $this->group->stream();
    }

    public function __toString()
    {
        return $this->name;
    }
}

<?php

namespace WebGarden\Messaging\Redis;

class Consumer
{
    protected string $name;

    protected Group $group;

    private static function determineDefaultName(Group $group): string
    {
        return "{$group}_client";
    }

    public function __construct(Group $group, ?string $name = null)
    {
        $this->name = $name ?: static::determineDefaultName($group);
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
}

<?php

namespace App\Domain\ValueObjects;

class Type
{
    private string $type;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function fromString(string $type): self
    {
        return new self($type);
    }

    public function asString(): string
    {
        return $this->type;
    }

}
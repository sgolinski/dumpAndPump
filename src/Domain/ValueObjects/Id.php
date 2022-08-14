<?php

namespace App\Domain\ValueObjects;

class Id
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = str_replace('/address/','', $id);
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function asString(): string
    {
        return $this->id;
    }
}
<?php

namespace App\Domain\ValueObjects;

class Address
{
    private string $address;

    private function __construct(string $address)
    {
        $this->address = str_replace(['/address/', '/tx/'], [''], $address);
    }

    public static function fromString(string $address): self
    {
        return new self($address);
    }

    public function asString(): string
    {
        return $this->address;
    }
}
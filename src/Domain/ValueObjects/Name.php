<?php

namespace App\Domain\ValueObjects;

use App\Application\Validation\Blacklisted;
use InvalidArgumentException;

class Name
{
    private string $name;

    private function __construct(string $name)
    {
        $this->ensureNameIsAllowed($name);
        $this->name = $name;
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function asString(): string
    {
        return $this->name;
    }

    private function ensureNameIsAllowed(string $name): void
    {
        if (in_array(strtolower($name), Blacklisted::NAMES)) {
            throw new InvalidArgumentException('Name is blacklisted');
        }
    }
}
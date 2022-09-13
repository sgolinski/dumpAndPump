<?php

namespace App\Domain\ValueObjects;


use App\Application\Validation\Allowed;
use InvalidArgumentException;

class Name
{
    private string $name;

    private function __construct(string $name)
    {
        // $this->ensureNameIsAllowed($name);
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

    public function ensureNameIsAllowed(): bool
    {
        if (in_array($this->name, Allowed::NAMES)) {
            return true;
        }

        return false;
    }
}
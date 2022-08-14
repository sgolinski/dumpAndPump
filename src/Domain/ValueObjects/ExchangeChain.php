<?php

namespace App\Domain\ValueObjects;

use App\Application\Validation\Allowed;

class ExchangeChain
{
    private string $exchangeChain;

    private function __construct(string $chain)
    {
        $this->ensureIsAllowedChain($chain);
        $this->exchangeChain = $chain;
    }

    public static function fromString(string $chain): self
    {
        return new self($chain);
    }

    public function asString(): string
    {
        return $this->exchangeChain;
    }

    private function ensureIsAllowedChain(string $chain): void
    {
        if (!in_array(strtolower($chain), Allowed::NAMES)) {
            throw new \InvalidArgumentException('Not Allowed Name for Chain');
        }
    }
}
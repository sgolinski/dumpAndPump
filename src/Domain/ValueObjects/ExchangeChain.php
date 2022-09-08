<?php

namespace App\Domain\ValueObjects;

class ExchangeChain
{
    private string $exchangeChain;

    private function __construct(string $chain)
    {

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


}
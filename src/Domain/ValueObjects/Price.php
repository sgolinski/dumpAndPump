<?php

namespace App\Domain\ValueObjects;

class Price
{
    private float $price;

    private function __construct(float $price)
    {
        return $this->price = $price;
    }

    public static function fromFloat(float $price): self
    {
        return new self($price);
    }

    public function asFloat()
    {
        return $this->price;
    }
}
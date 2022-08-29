<?php

namespace App\Domain\ValueObjects;

class Holders
{
    public int $holders = 0;
    public const MIN_AMOUNT_HOLDERS = 300;

    private function __construct(
        string $holders
    )
    {
        $number = $this->transformToInt($holders);
        $this->holders = $number;
    }

    public static function fromString(
        string $numOfHolders
    ): self
    {
        return new self($numOfHolders);
    }

    public function asInt(): int
    {
        return $this->holders;
    }

    public function __toString(): string
    {
        return $this->holders;
    }

    private function transformToInt(string $holders): int
    {
        $number = str_replace(",", '', $holders);
        return (int)$number;
    }

    public function enoughToTrust(): bool
    {
        return $this->asInt() > Holders::MIN_AMOUNT_HOLDERS;
    }
}
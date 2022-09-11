<?php

namespace App\Domain\ValueObjects;

class TxnHashId
{
    private string $txnHash;

    private function __construct(string $txnHash)
    {
        $this->txnHash = str_replace('/address/', '', $txnHash);
    }

    public static function fromString(string $txnHash): self
    {
        return new self($txnHash);
    }

    public function asString(): string
    {
        return $this->txnHash;
    }
}
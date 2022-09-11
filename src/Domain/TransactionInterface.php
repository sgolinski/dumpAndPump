<?php

namespace App\Domain;

use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\TxnHashId;
use App\Domain\ValueObjects\Type;

interface TransactionInterface
{
    public function price(): Price;

    public function type(): Type;

    public function name();

    public function txnHashId(): TxnHashId;
}
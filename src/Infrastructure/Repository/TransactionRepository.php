<?php

namespace App\Infrastructure\Repository;

use App\Domain\Transaction;

interface TransactionRepository
{
    public function save(Transaction $transaction);
}
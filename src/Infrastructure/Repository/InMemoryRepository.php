<?php

namespace App\Infrastructure\Repository;

use App\Domain\Transaction;

class InMemoryRepository implements TransactionRepository
{
    private array $transactionsInCache = [];

    public function byId($id): Transaction
    {
        return $this->transactionsInCache[$id];
    }

    public function hasId(string $key): bool
    {
        if (empty($this->transactionsInCache)) {
            return false;
        }
        return array_key_exists($key, $this->transactionsInCache);
    }

    public function all(): ?array
    {
        if (empty($this->transactionsInCache)) {
            return null;
        }
        return $this->transactionsInCache;
    }

    public function byRepetitions(): array
    {
        $repeated = [];
        foreach ($this->transactionsInCache as $transaction) {
            if ($transaction->showRepetitions() >= 2 && $transaction->ensurePriceIsHighEnough() ) {
                $repeated[] = $transaction;
            }
        }
        return $repeated;
    }

    public function byPrice(): array
    {
        $drops = [];
        foreach ($this->transactionsInCache as $transaction) {
            assert($transaction instanceof Transaction);
            if ($transaction->ensurePriceIsHighEnough()) {
                $drops[] = $transaction;
            }
        }
        return $drops;
    }

    public function isEmpty(): bool
    {
        return empty($this->transactionsInCache);
    }

    public function save(Transaction $transaction)
    {
        // TODO: Implement save() method.
    }

    public function add(string $key, Transaction $transaction)
    {
        $this->transactionsInCache[$key] = $transaction;
    }

    public function remove($key)
    {
        unset($this->transactionsInCache[$key]);
    }
}

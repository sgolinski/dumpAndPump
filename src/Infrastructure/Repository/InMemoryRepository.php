<?php

namespace App\Infrastructure\Repository;

use App\Domain\Transaction;
use App\Domain\TransactionInterface;
use App\Domain\ValueObjects\TxnHashId;

class InMemoryRepository implements TransactionRepository
{
    private array $transactionsInCache;
    private array $blockedTransactionsInCache;

    public function __construct()
    {
        $this->transactionsInCache = [];
    }

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
            assert($transaction instanceof Transaction);
            if ($transaction->showRepetitions() >= 2 && $transaction->ensurePriceIsHighEnough()) {
                $repeated[] = $transaction;
            } elseif ($transaction->showRepetitions() >= 5) {
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

    public function add(string $key, TransactionInterface $transaction)
    {
        if (!empty($this->blockedTransactionsInCache)) {
            if (isset($this->blockedTransactionsInCache[$key])) {
                return;
            }
        }
        if (isset($this->transactionsInCache[$key])) {
            foreach ($this->transactionsInCache[$key] as $txn) {
                assert($txn instanceof TransactionInterface);
                if ($transaction->price()->asFloat() == $txn->price()->asFloat()) {
                    return;
                }
            }
        }
        $this->transactionsInCache[$key][] = $transaction;
    }

    public function remove($key)
    {
        unset($this->transactionsInCache[$key]);
    }

    public function addToBlocked(TxnHashId $txnHash): void
    {
        $this->blockedTransactionsInCache[] = $txnHash->asString();
    }

    public function removeFromBlocked(TxnHashId $txnHash): void
    {
        if (empty($this->blockedTransactionsInCache)) {
            return;
        }
        $index = null;
        for ($i = 0; $i < count($this->blockedTransactionsInCache); $i++) {
            if ($txnHash->asString() == $this->blockedTransactionsInCache[$i]) {
                $index = $i;
                break;
            }
        }

        if ($index) {
            unset($this->blockedTransactionsInCache[$index]);
        }
    }

}

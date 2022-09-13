<?php

namespace App\Infrastructure\Repository;

use App\Domain\Transaction;
use App\Domain\TransactionInterface;
use App\Domain\ValueObjects\TxnHashId;
use ArrayIterator;
use SplDoublyLinkedList;

class InMemoryRepository implements TransactionRepository
{
    private array $transactionsInCache;
    private array $blockedTransactionsInCache;

    public function __construct()
    {
        $this->transactionsInCache = [];
        $this->blockedTransactionsInCache = [];
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
        if (in_array($txnHash->asString(), $this->blockedTransactionsInCache)) {
            return;
        }
        $this->blockedTransactionsInCache[] = $txnHash->asString();
    }

    public function removeFromBlocked(TxnHashId $txnHash): void
    {
        if (empty($this->blockedTransactionsInCache) && !in_array($txnHash->asString(), $this->blockedTransactionsInCache)) {
            return;
        }
        $index = null;

        $counter = count($this->blockedTransactionsInCache);
        for ($i = 0; $i < $counter; $i++) {
            if ($txnHash->asString() == $this->blockedTransactionsInCache[$i]) {
                $index = $i;
            }
        }
        if ($index) {
            unset($this->blockedTransactionsInCache[$index]);
        }
        $this->blockedTransactionsInCache = array_filter($this->blockedTransactionsInCache, 'strlen');
    }

}

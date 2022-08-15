<?php

namespace App\Application;

use App\Domain\Transaction;
use App\Infrastructure\Repository\InMemoryRepository;
use App\Infrastructure\TransactionFactory;
use ArrayIterator;
use Exception;
use InvalidArgumentException;

class WebElementService
{
    public TransactionFactory $factory;
    public InMemoryRepository $repository;

    public function __construct(InMemoryRepository $repository)
    {
        $this->factory = new TransactionFactory();
        $this->repository = $repository;
    }

    public function transformElementsToTransactions(array $webElements): array
    {
        foreach ($webElements as $webElement) {
            try {
                $this->ensureCacheIsNotEmpty($webElement);
                $transaction = $this->factory->createTransaction($webElement);

            } catch (Exception) {
                continue;
            }
            assert($transaction instanceof Transaction);
            $key = $transaction->id()->asString();

            if ($this->repository->isEmpty() || !$this->repository->hasId($key)) {
                $this->repository->add($key, $transaction);
                continue;
            }

            $currentTransaction = $this->repository->byId($key);
            if ($currentTransaction->priceEqualTo($transaction->price())) {
                continue;
            }

            $currentTransaction->noticeRepetitions();
        }
        return $this->repository->all();
    }

    private function ensureCacheIsNotEmpty(mixed $cache): void
    {
        if ($cache === null) {
            throw new InvalidArgumentException();
        }
    }
}
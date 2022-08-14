<?php

namespace App\Infrastructure\Repository;

use App\Application\Validation\Allowed;
use App\Application\Validation\Blacklisted;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Id;
use InvalidArgumentException;
use Predis\Client;

class RedisRepository
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client('127.0.0.1');
    }

    public function byIdComplete(Id $id): Transaction
    {
        $events = $this->client->hget('complete', $id->asString());
        $transaction = new Transaction($id);
        //todo
        $transaction->reconstitute($id->asString(), $events);
        return $transaction;
    }

    public function ensureHasNoStatus(Transaction $transaction): void
    {
        $statuses = [];
        foreach (Allowed::STATUSES as $key) {
            if ($this->client->hexists($key, $transaction->id->asString())) {
                $statuses[] = $key;
            }
        }
        if (in_array(Blacklisted::STATUSES, $statuses)) {
            throw new InvalidArgumentException('Status not Allowed');
        }
    }

    public function save(string $key, Transaction $transaction): void
    {
        $this->ensureCorrectKey($key);

        $this->client->hset($key, $transaction->id()->asString(), serialize($transaction->recordedEvents()));
    }

    private function ensureCorrectKey($key): void
    {
        if (!in_array($key, Allowed::STATUSES)) {
            throw new InvalidArgumentException('Wrong access key');
        }
    }

    public function size(): void
    {
        echo $this->client->dbsize();
    }

    public function findAll(string $key): array
    {
        $transactions = [];
        $serialized = $this->client->hgetall($key);

        foreach ($serialized as $key => $string) {
            $transactions[] = Transaction::reconstitute($key, $string);
        }

        return $transactions;
    }

    public function removeFrom(string $key, Transaction $transaction): void
    {
        $this->client->hdel($key, [$transaction->id()->asString()]);
    }

}
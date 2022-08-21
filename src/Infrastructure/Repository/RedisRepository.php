<?php

namespace App\Infrastructure\Repository;

use App\Application\Validation\Allowed;
use App\Application\Validation\Blacklisted;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Id;
use Exception;
use InvalidArgumentException;
use Predis\Client;

class RedisRepository
{
    private Client $client;

    public function __construct()
    {
        try {
            $this->client = new Client([
                'host' => '127.0.0.1'
            ]);
        } catch (Exception $exception) {
            echo 'Not connected';
        }
    }

    public function byIdComplete(Id $id): Transaction
    {
        $events = $this->client->hget('complete', $id->asString());
        $transaction = new Transaction($id);
        $transaction->reconstitute($id->asString(), $events);
        return $transaction;
    }

    public function byId(string $id): ?string
    {
        foreach (Allowed::STATUSES as $key) {
            if ($this->client->hexists($key, $id)) {
                return $this->client->hget($key, $id);
            }
        }
        return null;
    }

    public function ensureHasAllowedStatus(Transaction $transaction): bool
    {
        $statuses = [];
        foreach (Allowed::STATUSES as $key) {
            if ($this->client->hexists($key, $transaction->id->asString())) {
                echo $key . ' ' . $transaction->name->asString() . ' ' . $transaction->id->asString() . PHP_EOL;
                $statuses[] = $key;
            }
        }
        if (in_array($statuses, Blacklisted::STATUSES)) {
            return false;
        }
        return true;
    }

    public function save(
        string      $key,
        Transaction $transaction
    ): void
    {
        $this->ensureCorrectKey($key);
        $this->client->hset($key, $transaction->id()->asString(), serialize($transaction->recordedEvents()));
        if ($key !== 'blacklisted') {
            $this->client->expireat($key, 3600);
        }
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

        foreach ($serialized as $id => $string) {
            $allowToComplete = $this->checkIfIsSentOrBlacklisted($id);
            if (!$allowToComplete) {
                $this->client->hdel($key, [$id]);
                continue;
            }
            $transactions[] = Transaction::reconstitute($id, $string);
        }
        return $transactions;
    }

    public function removeFrom(string $key, Transaction $transaction): void
    {
        $this->client->hdel($key, [$transaction->id()->asString()]);
    }

    public function saveDb(): void
    {
        $this->client->save();
    }

    public function checkIfIsSentOrBlacklisted(string $id): bool
    {
        $isSent = $this->client->hexists(Blacklisted::STATUSES[0], $id);
        $isBlacklisted = $this->client->hexists(Blacklisted::STATUSES[1], $id);

        return $isSent == false && $isBlacklisted == false;
    }

}
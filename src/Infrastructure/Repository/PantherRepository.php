<?php

namespace App\Infrastructure\Repository;

use App\Application\Validation\Selectors;
use App\Domain\Transaction;
use App\Domain\ValueObjects\Url;
use ArrayIterator;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Panther\Client;

class PantherRepository implements TransactionRepository
{
    private Client $client;

    public function findElements(Url $url): ?ArrayIterator
    {
        $this->ensureIsNotBusy($url);
        $this->refreshClient($url);
        try {
            return $this->client->getCrawler()
                ->filter(Selectors::FOR_TABLE)
                ->filter(Selectors::FOR_TABLE_BODY)
                ->children()
                ->getIterator();
        } catch (Exception $exception) {
            $this->client->reload();
        }
        return null;
    }

    public function findOneElementOn(Url $url): string
    {
        try {
            $this->refreshClient($url);
            return $this->client->getCrawler()
                ->filter(Selectors::FOR_HOLDERS)
                ->getText();

        } catch (Exception $exception) {
            $this->client->reload();
            throw  new InvalidArgumentException('Holders unreachable for address ' . $url->asString());
        }
    }

    private function refreshClient(Url $url): void
    {
        $this->client = Client::createChromeClient();
        $this->client->start();
        $this->client->get($url->asString());
        $this->client->refreshCrawler();
    }

    private function ensureIsNotBusy(Url $url): void
    {
        if ($url->asString() === 'https://bscscan.com/busy') {
            sleep(12);
            $this->client->reload();
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function save(Transaction $transaction)
    {
        // TODO: Implement save() method.
    }
}

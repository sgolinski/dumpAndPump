<?php

namespace App\Application;

use App\Application\Validation\Selectors;
use App\Domain\ValueObjects\Url;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Panther\Client;


class PantherService
{
    private Client $client;
    private array $elements = [];

    public function __construct()
    {
        $this->client = Client::createChromeClient();

    }

    public function saveWebElements(Url $url): void
    {
        $this->ensureIsNotBusy($url);
        $this->refreshClient($url);
        try {
            $this->elements = $this->client->getCrawler()
                ->filter(Selectors::FOR_TABLE)
                ->filter(Selectors::FOR_TABLE_BODY)
                ->children()->getIterator()->getArrayCopy();
            var_dump($this->elements);
            die;
        } catch (Exception $exception) {
            $this->client->reload();
        }
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

    public function savedWebElements(): array
    {
        return $this->elements;
    }

}

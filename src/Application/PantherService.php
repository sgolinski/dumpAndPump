<?php

namespace App\Application;

use App\Application\Validation\RouterSelectors;
use App\Domain\ValueObjects\Url;
use Symfony\Component\Panther\Client;

class PantherService
{
    private Client $client;
    private array $elements = [];

    public function __construct()
    {
        $this->client = Client::createFirefoxClient();
    }

    public function saveWebElements(Url $url): void
    {
        $this->ensureIsNotBusy($url);
        $this->refreshClient($url);

        $this->elements = $this->client->getCrawler()
            ->filter(RouterSelectors::FOR_CONTENT_TABLE)
            ->children()
            ->getIterator()
            ->getArrayCopy();
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

<?php

namespace App\Domain\Event;

use App\Application\Validation\Urls;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Url;
use App\Infrastructure\DomainEvent;
use DateTimeImmutable;

class TransactionCompleted implements DomainEvent
{
    private Id $id;
    private Url $url;

    public function __construct($id)
    {
        $this->url = Url::fromString(Urls::FOR_TRANSACTION . $id->asString());
        $this->id = $id;
    }

    public function url(): Url
    {
        return $this->url;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function id(): Id
    {
        return $this->id;
    }
}

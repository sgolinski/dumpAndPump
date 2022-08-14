<?php

namespace App\Application;

use App\Application\Validation\Urls;
use App\Domain\ValueObjects\Url;

class ImportTransaction
{
    private Url $url;

    public function __construct(
        int $startPage,
    )
    {
        $this->url = Url::fromString(Urls::FOR_COMMAND . $startPage);
    }

    public function url(): Url
    {
        return $this->url;
    }
}
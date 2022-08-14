<?php

namespace App\Application;

class ImportTransaction
{
    private int $startPage;
    private int $endPage;

    public function __construct(
        int $startPage,
        int $endPage
    )
    {
        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    public function startPage(): int
    {
        return $this->startPage;
    }

    public function endPage(): int
    {
        return $this->endPage;
    }
}
<?php

namespace Domain\Event\Sourcing;

final class EventStreamId
{
    private string $streamName;

    private int $streamVersion;

    public function __construct(string $aStreamName, int $aStreamVersion = 1)
    {
        $this->setStreamName($aStreamName);
        $this->setStreamVersion($aStreamVersion);
    }

    public function streamName(): string
    {
        return $this->streamName;
    }

    public function streamVersion(): int
    {
        return $this->streamVersion;
    }

    public function withStreamVersion($aStreamVersion): EventStreamId
    {
        return new EventStreamId($this->streamName(), $aStreamVersion);
    }

    private function setStreamName($aStreamName): void
    {
        $this->streamName = $aStreamName;
    }

    private function setStreamVersion($aStreamVersion): void
    {
        $this->streamVersion = $aStreamVersion;
    }
}

<?php

namespace App\Utils;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class MockedClock implements ClockInterface
{
    private string $time = 'now';

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->time);
    }

    public function set(string $time)
    {
        $this->time = $time;
    }
}

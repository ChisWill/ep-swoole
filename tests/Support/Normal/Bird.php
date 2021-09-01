<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Normal;

final class Bird implements FlightInterface
{
    private int $speed;

    public function __construct(int $speed = 50)
    {
        $this->speed = $speed;
    }

    public function fly(): void
    {
        echo 'Bird\'s speed is ' . $this->speed;
    }
}

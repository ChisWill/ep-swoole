<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Normal;

final class Eagle
{
    private string $name;
    private FlightInterface $flight;

    public function __construct(string $name, FlightInterface $flight)
    {
        $this->name = $name;
        $this->flight = $flight;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function attack()
    {
        $this->flight->fly();

        echo sprintf('%s is attacking', $this->name);
    }
}

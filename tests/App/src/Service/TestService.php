<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Helper\Str;

class TestService
{
    public function getRandomString(): string
    {
        return Str::random();
    }
}

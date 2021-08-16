<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Throwable;

interface EventErrorRendererInterface
{
    public function render(Throwable $t, string $event, array $arguments): void;
}

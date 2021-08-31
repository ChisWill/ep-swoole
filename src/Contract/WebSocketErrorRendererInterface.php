<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep\Swoole\WebSocket\Request;
use Throwable;

interface WebSocketErrorRendererInterface
{
    public function render(Throwable $t, Request $request): void;
}

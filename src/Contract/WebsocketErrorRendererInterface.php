<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep\Swoole\WebSocket\Request;
use Throwable;

interface WebsocketErrorRendererInterface
{
    public function render(Throwable $t, Request $request): void;

    public function log(Throwable $t, Request $request): void;
}

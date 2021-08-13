<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Swoole\Contract\WebsocketErrorRendererInterface;
use Ep\Swoole\WebSocket\Request;
use Throwable;

class WebsocketErrorRenderer implements WebsocketErrorRendererInterface
{
    public function render(Throwable $t, Request $request): void
    {
        $request->emit('error',  $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine());
    }

    public function log(Throwable $t, Request $request): void
    {
        Ep::getLogger()->emergency($t->getMessage(), [
            'detail' => $t->getTraceAsString()
        ]);
    }
}

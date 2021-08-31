<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Swoole\Contract\WebSocketErrorRendererInterface;
use Ep\Swoole\WebSocket\Request;
use Throwable;

class WebSocketRenderer implements WebSocketErrorRendererInterface
{
    public function render(Throwable $t, Request $request): void
    {
        $request->emit('error',  $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine());

        $this->log($t, $request);
    }

    private function log(Throwable $t, Request $request): void
    {
        Ep::getLogger()->emergency($t->getMessage(), [
            'detail' => $t->getTraceAsString()
        ]);
    }
}

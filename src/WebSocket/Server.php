<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep;
use Ep\Base\ControllerLoader;
use Ep\Base\Route;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\SwooleEvent;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Throwable;

class Server extends HttpServer
{
    /**
     * {@inheritDoc}
     */
    protected function getServerClass(): string
    {
        return WebSocketServer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function onRequest(): void
    {
        $this->getServer()->on(SwooleEvent::ON_MESSAGE, function (WebSocketServer $server, Frame $frame): void {
            $this->handleMessage(new Socket($server, $frame));
        });

        $this->getServer()->on(SwooleEvent::ON_REQUEST, [$this, 'handleRequest']);
    }

    private function handleMessage(Socket $socket): void
    {
        try {
            [$ok, $handler,] = Ep::getDi()->get(Route::class)->match($socket->getRoute());
            $loader = Ep::getInjector()
                ->make(ControllerLoader::class, [
                    'suffix' => $this->config->socketSuffix
                ])
                ->parse($handler);
            call_user_func([$loader->getController(), $loader->getAction()], $socket);
        } catch (Throwable $t) {
            $socket->emit($t->getMessage());
        }
    }
}

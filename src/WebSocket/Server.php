<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\SwooleEvent;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

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
        $this->getServer()->on(SwooleEvent::ON_MESSAGE, function (WebSocketServer $server, Frame $frame) {
            $server->push($frame->fd, $frame->data);
        });

        $this->getServer()->on(SwooleEvent::ON_REQUEST, [$this, 'handleRequest']);
    }
}

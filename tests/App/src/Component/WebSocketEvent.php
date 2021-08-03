<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Swoole\Http\PsrRequestFactory;
use Swoole\Http\Request;
use Swoole\WebSocket\Server;

class WebSocketEvent
{
    /**
     * @Inject
     */
    private PsrRequestFactory $psrRequestFactory;

    public function onOpen(Server $server, Request $request)
    {
        $psrRequest = $this->psrRequestFactory->createFromSwooleRequest($request);
        $token = $psrRequest->getQueryParams()['token'] ?? '';
        if (!$token) {
            $server->close($request->fd);
        }
    }
}

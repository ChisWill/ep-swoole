<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Swoole\Http\PsrRequestFactory;
use Swoole\Http\Request;
use Swoole\Timer;
use Swoole\WebSocket\Server;

class WebSocketEvent
{
    /**
     * @Inject
     */
    private PsrRequestFactory $psrRequestFactory;
    /**
     * @Inject
     */
    private AuthMethod $auth;

    public function onOpen(Server $server, Request $request)
    {
        $psrRequest = $this->psrRequestFactory->createFromSwooleRequest($request);
        if ($psrRequest->getUri()->getPath() !== '/') {
            $identity = $this->auth
                ->withFd($request->fd)
                ->withTokenType('1')
                ->authenticate($psrRequest);
            if (!$identity) {
                $server->close($request->fd);
            } else {
                echo $identity->getId();
            }
        }
    }

    public function onWorkerStart(Server $server, int $workerId)
    {
        Timer::tick(1000, function () use ($server, $workerId) {
        });
    }

    public function onWorkerStop(Server $server, int $workId)
    {
    }

    public function onClose(Server $server, int $fd)
    {
        echo $fd . ' close';
    }
}

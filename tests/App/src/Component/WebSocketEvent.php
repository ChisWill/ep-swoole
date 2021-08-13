<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Auth\AuthRepository;
use Ep\Swoole\Http\PsrRequestFactory;
use Swoole\Http\Request;
use Swoole\Timer;
use Swoole\WebSocket\Server;
use Yiisoft\Auth\Method\QueryParameter;

class WebSocketEvent
{
    /**
     * @Inject
     */
    private PsrRequestFactory $psrRequestFactory;

    /**
     * @Inject
     */
    private AuthRepository $auth;

    /**
     * @Inject
     */
    private SocketFdRepository $socketFd;

    public function onOpen(Server $server, Request $request)
    {
        $server->tokenType = '1';
        $psrRequest = $this->psrRequestFactory->createFromSwooleRequest($request);
        if ($psrRequest->getUri()->getPath() !== '/') {
            /** @var QueryParameter */
            $method = $this->auth->findMethod(QueryParameter::class);
            $identity = $method
                ->withTokenType($server->tokenType)
                ->authenticate($psrRequest);
            if (!$identity) {
                $server->close($request->fd);
            } else {
                $accessToken = $psrRequest->getQueryParams()['access-token'] ?? null;
                $this->socketFd->update($request->fd, $identity->getId(), $accessToken);
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

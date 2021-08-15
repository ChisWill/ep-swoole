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

    public function onTask(Server $server, $taskId, $reactorId, $data)
    {
        $r = $server->push($data['fd'], $this->encode($data['content']));
        if ($r) {
            $server->finish($data['self']);
        }
    }

    public function onFinish(Server $server, $taskId, $data)
    {
        $server->push($data, $this->encode([
            'event' => 'msg',
            'type' => 'system',
            'data' => '推送成功'
        ]));
    }

    private function encode($data): string
    {
        return json_encode([$data['event'], $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

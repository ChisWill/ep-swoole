<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Base\Route;
use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\SwooleEvent;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Throwable;

final class Server implements ServerInterface
{
    use ServerTrait;

    private Config $config;
    private Factory $factory;
    private HttpServer $httpServer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        Config $config,
        Factory $factory,
        HttpServer $httpServer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = $config;
        $this->factory = $factory;
        $this->httpServer = $httpServer;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
    }

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
            $this->handleMessage($this->factory->createRequest($server, $frame));
        });

        $this->getServer()->on(SwooleEvent::ON_REQUEST, [$this->httpServer, 'handleRequest']);
    }

    private function handleMessage(Request $request): void
    {
        try {
            [, $handler] = $this->route->match($request->getRoute());

            $this->controllerRunner
                ->withControllerSuffix($this->config->webSocketSuffix)
                ->run($handler, $request);
        } catch (Throwable $t) {
            $request->emit('error', $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine());
        }
    }
}

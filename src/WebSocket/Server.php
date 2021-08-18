<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Base\Route;
use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\Http\Server as HttpServer;
use Swoole\Constant;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Throwable;

/**
 * @method WebSocketServer getServer()
 */
final class Server implements ServerInterface
{
    use ServerTrait;

    private Config $config;
    private Factory $factory;
    private HttpServer $httpServer;
    private Route $route;
    private ControllerRunner $controllerRunner;
    private ErrorRenderer $errorRenderer;

    public function __construct(
        Config $config,
        Factory $factory,
        HttpServer $httpServer,
        Route $route,
        ControllerRunner $controllerRunner,
        ErrorRenderer $errorRenderer
    ) {
        $this->config = $config;
        $this->factory = $factory;
        $this->httpServer = $httpServer;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
        $this->errorRenderer = $errorRenderer;
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
        $this->getServer()->on(Constant::EVENT_MESSAGE, [$this, 'handleMessage']);

        $this->getServer()->on(Constant::EVENT_REQUEST, [$this->httpServer, 'handleRequest']);
    }

    public function handleMessage(WebSocketServer $server, Frame $frame): void
    {
        try {
            $request = $this->factory->createRequest($server, $frame);

            [, $handler] = $this->route->match($request->getRoute());

            $this->controllerRunner
                ->withControllerSuffix($this->config->webSocketSuffix)
                ->run($handler, $request);
        } catch (Throwable $t) {
            $this->errorRenderer->render($t, $request);
        }
    }
}

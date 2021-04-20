<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Ep\Contract\ErrorRendererInterface;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\Http\Emitter;
use Ep\Swoole\SwooleEvent;
use Ep\Web\Application as WebApplication;
use Ep\Web\Service;
use Yiisoft\Http\Method;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Server implements ServerInterface
{
    use ServerTrait;

    private WebApplication $webApplication;
    private ServerRequestFactory $serverRequestFactory;
    private ErrorRendererInterface $errorRenderer;
    private Service $service;

    public function __construct(
        WebApplication $webApplication,
        ServerRequestFactory $serverRequestFactory,
        ErrorRendererInterface $errorRenderer,
        Service $service
    ) {
        $this->webApplication = $webApplication;
        $this->serverRequestFactory = $serverRequestFactory;
        $this->errorRenderer = $errorRenderer;
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function getServerClass(): string
    {
        return HttpServer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function onRequest(): void
    {
        $this->getServer()->on(SwooleEvent::ON_REQUEST, [$this, 'handleRequest']);
    }

    public function handleRequest(Request $swooleRequest, Response $swooleResponse): void
    {
        try {
            $psrRequest = $this->serverRequestFactory->createFromSwooleRequest($swooleRequest);

            $this->send(
                $psrRequest,
                $this->webApplication->handleRequest($psrRequest),
                $swooleResponse
            );
        } catch (Throwable $t) {
            $swooleResponse->end($this->errorRenderer->render($t, $psrRequest));
        }
    }

    private function send(ServerRequestInterface $psrRequest, ResponseInterface $psrResponse, Response $swooleResponse): void
    {
        (new Emitter($swooleResponse))->emit($psrResponse, $psrRequest->getMethod() === Method::HEAD);
    }
}

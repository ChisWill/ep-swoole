<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\SwooleEvent;
use Ep\Web\Application as WebApplication;
use Ep\Web\ErrorRenderer;
use Ep\Web\Service;
use Yiisoft\Http\Method;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @method HttpServer getServer()
 */
final class Server implements ServerInterface
{
    use ServerTrait;

    private Config $config;
    private WebApplication $webApplication;
    private PsrRequestFactory $psrRequestFactory;
    private ErrorRenderer $errorRenderer;
    private Service $service;

    public function __construct(
        Config $config,
        WebApplication $webApplication,
        PsrRequestFactory $psrRequestFactory,
        ErrorRenderer $errorRenderer,
        Service $service
    ) {
        $this->config = $config;
        $this->webApplication = $webApplication;
        $this->psrRequestFactory = $psrRequestFactory;
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
            $psrRequest = $this->psrRequestFactory->createFromSwooleRequest($swooleRequest);

            $this->emit(
                $psrRequest,
                $this->webApplication->handleRequest($psrRequest),
                $swooleResponse
            );
        } catch (Throwable $t) {
            $this->errorRenderer->log($t, $psrRequest);
            $swooleResponse->end($this->errorRenderer->render($t, $psrRequest));
        }
    }

    private function emit(ServerRequestInterface $psrRequest, ResponseInterface $psrResponse, Response $swooleResponse): void
    {
        (new SapiEmitter($swooleResponse))->emit(
            $psrResponse,
            $psrRequest->getMethod() === Method::HEAD
        );
    }
}

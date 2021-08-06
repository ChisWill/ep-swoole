<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Base\ErrorRenderer as BaseErrorRenderer;
use Ep\Swoole\Contract\WebsocketErrorRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorRenderer extends BaseErrorRenderer
{
    private ContainerInterface $container;
    private LoggerInterface $logger;

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     */
    public function render(Throwable $t, $request): string
    {
        if ($this->container->has(WebsocketErrorRendererInterface::class)) {
            $this->container
                ->get(WebsocketErrorRendererInterface::class)
                ->render($t, $request);
        } else {
            $request->emit('error', parent::render($t, $request));
        }
        return '';
    }

    /**
     * @param Request $request
     */
    public function log(Throwable $t, $request): void
    {
        if ($this->container->has(WebsocketErrorRendererInterface::class)) {
            $this->container
                ->get(WebsocketErrorRendererInterface::class)
                ->log($t, $request);
        } else {
            $context = [
                'category' => get_class($t)
            ];

            $context['route'] = $request->getRoute();
            $context['data'] = $request->getData();

            $this->logger->error(parent::render($t, $request), $context);
        }
    }
}

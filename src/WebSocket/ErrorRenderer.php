<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Base\ErrorRenderer as BaseErrorRenderer;
use Ep\Swoole\Contract\WebSocketErrorRendererInterface;
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
        if ($this->container->has(WebSocketErrorRendererInterface::class)) {
            $this->container
                ->get(WebSocketErrorRendererInterface::class)
                ->render($t, $request);
        } else {
            $this->log($t, $request);

            $request->emit('error', parent::render($t, $request));
        }
        return '';
    }

    /**
     * @param Request $request
     */
    private function log(Throwable $t, $request): void
    {
        $context = [
            'category' => get_class($t)
        ];

        $context['route'] = $request->getRoute();
        $context['data'] = $request->getData();

        $this->logger->error(parent::render($t, $request), $context);
    }
}

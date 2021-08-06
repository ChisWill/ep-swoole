<?php

declare(strict_types=1);

namespace Ep\Swoole\Tcp;

use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\SwooleEvent;
use Swoole\Server as SwooleServer;

/**
 * @method SwooleServer getServer()
 */
class Server implements ServerInterface
{
    use ServerTrait;

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    protected function getServerClass(): string
    {
        return SwooleServer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function onRequest(): void
    {
        $this->getServer()->on(SwooleEvent::ON_RECEIVE, function (SwooleServer $server, int $fd, int $reactorId, string $data) {
            echo "[#" . $server->worker_id . "][@{$reactorId}]\tClient[$fd]: $data\n";
        });
    }
}

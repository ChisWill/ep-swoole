<?php

declare(strict_types=1);

namespace Ep\Swoole\Tcp;

use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Swoole\Constant;
use Swoole\Server as SwooleServer;
use LogicException;

/**
 * @method SwooleServer getServer()
 */
final class Server implements ServerInterface
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
    protected function bootstrap(SwooleServer $server): void
    {
        if (!isset($this->config->events[Constant::EVENT_RECEIVE])) {
            throw new LogicException('The "events" configuration must set ' . Constant::EVENT_RECEIVE . ' callback.');
        }
        $server->on(Constant::EVENT_RECEIVE, $this->config->events[Constant::EVENT_RECEIVE]);
    }
}

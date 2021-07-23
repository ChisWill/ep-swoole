<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ContextTrait;
use Ep\Contract\ControllerInterface;
use Ep\Contract\FilterTrait;
use LogicException;

abstract class Controller implements ControllerInterface
{
    use ContextTrait, FilterTrait, ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public string $id;
    /**
     * {@inheritDoc}
     */
    public string $actionId;

    public function before(Socket $socket): bool
    {
        return true;
    }

    public function after(Socket $socket): void
    {
    }

    public function getMiddlewares(): array
    {
        throw new LogicException('WebSocket doesn\'t have middlewares yet.');
    }

    public function setMiddlewares(array $middlewares): void
    {
        throw new LogicException('WebSocket doesn\'t have middlewares yet.');
    }
}

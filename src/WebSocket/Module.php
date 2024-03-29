<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Contract\FilterTrait;
use Ep\Contract\ModuleInterface;
use LogicException;

abstract class Module implements ModuleInterface
{
    use FilterTrait;

    public function before(Request $request): bool
    {
        return true;
    }

    public function after(Request $request): void
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

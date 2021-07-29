<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Contract\InjectorInterface;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

final class Factory
{
    private InjectorInterface $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    public function createRequest(Server $server, Frame $frame): Request
    {
        return $this->injector->make(Request::class, [$server, $frame]);
    }
}

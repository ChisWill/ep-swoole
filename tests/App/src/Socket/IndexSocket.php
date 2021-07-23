<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Socket;

class IndexSocket extends Controller
{
    public function before(Socket $socket): bool
    {
        $socket->emit('before');
        return true;
    }

    public function after(Socket $socket): void
    {
        $socket->emit('after');
    }

    public function indexAction(Socket $socket)
    {
        $params = [
            'id' => $this->id,
            'actionId' => $this->actionId,
            'route' => $socket->getRoute(),
            'receive' => $socket->getData(),
        ];
        $socket->emit($params);
    }
}

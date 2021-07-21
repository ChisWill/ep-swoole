<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Swoole\WebSocket\Socket;
use Ep\Tests\App\Component\Controller;

class IndexSocket extends Controller
{
    public function indexAction(Socket $socket)
    {
        $socket->emit('Index Received ' . mt_rand(0, 100) . '-' . ($socket->isExists() ? '1' : '2'));
    }
}

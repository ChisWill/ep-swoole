<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Swoole\WebSocket\Socket;
use Ep\Tests\App\Component\Controller;

class UserSocket extends Controller
{
    public function indexAction(Socket $socket)
    {
        $socket->emit('Received ' . mt_rand(0, 100) . '-' . ($socket->isExists() ? '1' : '2'));
    }
}

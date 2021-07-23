<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Socket;

class UserSocket extends Controller
{
    public function indexAction(Socket $socket)
    {
        $data = [
            'id' => 1,
            'name' => 'a'
        ];
        $socket->emit($data);
    }

    public function loginAction(Socket $socket)
    {
        // todo
    }
}

<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Request;

class IndexSocket extends Controller
{
    public function before(Request $request): bool
    {
        $request->emit('before');
        return true;
    }

    public function after(Request $request): void
    {
        $request->emit('after');
    }

    public function indexAction(Request $request)
    {
        $params = [
            'id' => $this->id,
            'actionId' => $this->actionId,
            'route' => $request->getRoute(),
            'receive' => $request->getData(),
        ];
        $request->emit($params);
    }
}

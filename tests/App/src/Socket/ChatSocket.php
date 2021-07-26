<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Socket;
use Ep\Tests\App\Service\ChatService;

class ChatSocket extends Controller
{
    /**
     * @Inject
     */
    private ChatService $chatService;

    public function sendTextAction(Socket $socket)
    {
        if ($this->chatService->isGuest($socket)) {
            $this->emit($socket, 'Login Required.', 'system');
            return;
        }

        $this->chatService->sendTarget($socket, $socket->getData());
    }

    private function emit(Socket $socket, $data, string $type = 'msg'): void
    {
        $socket->emit([
            'type' => $type,
            'target' => 'target',
            'data' => $data
        ]);
    }
}

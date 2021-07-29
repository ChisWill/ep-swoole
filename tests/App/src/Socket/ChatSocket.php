<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Request;
use Ep\Tests\App\Service\ChatService;

class ChatSocket extends Controller
{
    /**
     * @Inject
     */
    private ChatService $chatService;

    public function sendTextAction(Request $request)
    {
        if ($this->chatService->isGuest($request)) {
            $this->emit($request, 'Login Required.', 'system');
            return;
        }

        $this->chatService->sendTarget($request, $request->getData());
    }

    public function sendRoomTextAction(Request $request)
    {
        if ($this->chatService->isGuest($request)) {
            $this->emit($request, 'Login Required.', 'system');
            return;
        }

        $this->chatService->broadcast($request, $request->getData());
    }

    private function emit(Request $request, $data, string $type = 'msg'): void
    {
        $request->emit([
            'type' => $type,
            'target' => 'target',
            'data' => $data
        ]);
    }
}

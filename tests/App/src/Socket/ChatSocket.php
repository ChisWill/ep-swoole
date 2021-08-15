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

        $this->chatService->broadcast('msg', $request, $request->getData());
    }

    public function pushAction(Request $request)
    {
        if ($this->chatService->isGuest($request)) {
            $this->emit($request, 'Login Required.', 'system');
            return;
        }

        $data = $request->getData();
        $targetId = $data['id'];
        if (!$request->isOnline($targetId)) {
            $this->emit($request, '对方不在线.', 'system');
            return;
        }
        if ($targetId === $request->getId()) {
            $this->emit($request, '不能对自己发', 'system');
            return;
        }

        $fd = $request->getFd($targetId);
        $content = [
            'event' => 'msg',
            'type' => 'msg',
            'target' => 'target',
            'data' => $data['content']
        ];
        // 直接发
        $request->send('msg', $targetId, $content);
        // 后台发
        $request->getServer()->task([
            'self' => $request->getFd($request->getId()),
            'fd' => $fd,
            'content' => $content
        ]);
    }

    private function emit(Request $request, $data, string $type = 'msg'): void
    {
        $request->emit('msg', [
            'type' => $type,
            'target' => 'target',
            'data' => $data
        ]);
    }
}

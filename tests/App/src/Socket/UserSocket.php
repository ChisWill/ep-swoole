<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Socket;
use Ep\Tests\App\Service\ChatService;

class UserSocket extends Controller
{
    /**
     * @Inject
     */
    private ChatService $chatService;

    private array $userList = [
        1 => [
            'name' => 'Chris'
        ],
        2 => [
            'name' => 'Leo'
        ],
        3 => [
            'name' => 'Jack'
        ]
    ];

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
        if (!$this->chatService->isGuest($socket)) {
            $this->emit($socket, 'Logined.');
            return;
        }

        $id = (int) $socket->getData();
        if (!array_key_exists($id, $this->userList)) {
            $this->emit($socket, 'Id is invalid.');
            return;
        }

        if ($this->chatService->isUsed($socket, $id)) {
            $this->emit($socket, $this->userList[$id]['name'] . ' had been used.');
            return;
        }

        $info = [
            'id' => $id,
        ] + $this->userList[$id];

        $this->chatService->addUser($socket, $info);

        $this->emit($socket, 'Welcome ' . $info['name']);
    }

    public function logoutAction(Socket $socket)
    {
        $this->chatService->removeUser($socket);
    }

    private function emit(Socket $socket, $data): void
    {
        $socket->emit([
            'type' => 'system',
            'data' => $data
        ]);
    }
}

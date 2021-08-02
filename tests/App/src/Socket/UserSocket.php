<?php

declare(strict_types=1);

namespace Ep\Tests\App\Socket;

use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Controller;
use Ep\Swoole\WebSocket\Request;
use Ep\Tests\App\Service\ChatService;
use Throwable;

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
        ],
        4 => [
            'name' => 'Rose'
        ],
        5 => [
            'name' => 'Peter'
        ],
        6 => [
            'name' => 'Mary'
        ]
    ];

    public function indexAction(Request $request)
    {
        $data = [
            'id' => 1,
            'name' => 'a'
        ];
        $request->emit('msg', $data);
    }

    public function loginAction(Request $request)
    {
        if (!$this->chatService->isGuest($request)) {
            $this->emit($request, 'Logined.');
            return;
        }

        $id = (int) $request->getData();
        if (!array_key_exists($id, $this->userList)) {
            $this->emit($request, 'Id is invalid.');
            return;
        }

        if ($this->chatService->isUsed($request, $id)) {
            $this->emit($request, $this->userList[$id]['name'] . ' had been used.');
            return;
        }

        $info = [
            'id' => $id,
        ] + $this->userList[$id];

        $this->chatService->addUser($request, $info);

        $this->emit($request, 'Welcome ' . $info['name']);
    }

    public function roomAction(Request $request)
    {
        try {
            $this->chatService->enterRoom($request, $request->getData());
        } catch (Throwable $t) {
            echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine();
        }
    }

    public function logoutAction(Request $request)
    {
        $this->chatService->removeUser($request);
    }

    private function emit(Request $request, $data): void
    {
        $request->emit('msg', [
            'type' => 'system',
            'data' => $data
        ]);
    }
}

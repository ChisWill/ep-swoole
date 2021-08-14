<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Swoole\WebSocket\Request;

final class ChatService
{
    public function isGuest(Request $request): bool
    {
        return $request->isGuest();
    }

    //-------------------------------------------------
    //                简单聊天
    //-------------------------------------------------

    private array $users = [];

    public function isUsed(Request $request, int $id): bool
    {
        if (!isset($this->users[$id])) {
            return false;
        }
        $usedFd = $this->users[$id];
        $selfFd = $request->getFrame()->fd;
        if ($usedFd === $selfFd) {
            return false;
        }
        foreach ($request->getServer()->connections as $fd) {
            if ($fd === $usedFd) {
                return true;
            }
        }
        unset($request->getServer()->info[$this->users[$id]]);
        unset($this->users[$id]);
        return false;
    }

    public function addUser(Request $request, array $info): void
    {
        $request->getServer()->info[$request->getFrame()->fd] = $info;
        $this->users[$info['id']] = $request->getFrame()->fd;
    }

    public function getSelfInfo(Request $request): array
    {
        return $request->getServer()->info[$request->getFrame()->fd];
    }

    public function removeUser(Request $request): void
    {
        if (!$this->isGuest($request)) {
            unset($this->users[$this->getSelfInfo($request)['id']]);
            unset($request->getServer()->info[$request->getFrame()->fd]);
        }
    }

    /**
     * @return mixed
     */
    public function getTarget(Request $request)
    {
        $self = $request->getFrame()->fd;
        foreach ($request->getServer()->info as $fd => $info) {
            if ($fd !== $self) {
                if ($request->isOnline($fd)) {
                    return $fd;
                } else {
                    unset($request->getServer()->info[$fd]);
                }
            }
        }
        return null;
    }

    public function sendTarget(Request $request, $data): void
    {
        $target = $this->getTarget($request);
        if ($target === null) {
            $request->emit('msg', [
                'type' => 'system',
                'data' => '还没有目标'
            ]);
            return;
        }

        if ($request->isOnline($target)) {
            $request->emit('msg', [
                'type' => 'msg',
                'target' => 'target',
                'data' => $data
            ], $target);
        } else {
            $request->emit('msg', [
                'type' => 'system',
                'data' => '对方不在线'
            ]);
        }
    }

    //-------------------------------------------------
    //                聊天室
    //-------------------------------------------------

    public function enterRoom(Request $request, string $room): void
    {
        $request->join($room);

        $request->emit('msg', [
            'type' => 'system',
            'data' => '你进入了房间"' . $room . '"'
        ]);
    }

    public function broadcast(string $event, Request $request, $data): void
    {
        if (!$request->isIn($data['room'])) {
            $request->emit($event, [
                'type' => 'system',
                'data' => '你不在当前房间'
            ]);
        } else {
            $request->broadcast($event, $data['room'], [
                'type' => 'msg',
                'target' => 'target',
                'data' => $data['text']
            ]);
        }
    }
}

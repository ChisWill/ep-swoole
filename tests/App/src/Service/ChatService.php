<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Swoole\WebSocket\Socket;

final class ChatService
{
    private function init(Socket $socket)
    {
        $socket->getServer()->info ??= [];
    }

    public function isGuest(Socket $socket): bool
    {
        $this->init($socket);

        return !isset($socket->getServer()->info[$socket->getFrame()->fd]);
    }

    private array $users = [];

    public function isUsed(Socket $socket, int $id): bool
    {
        if (!isset($this->users[$id])) {
            return false;
        }
        $usedFd = $this->users[$id];
        $selfFd = $socket->getFrame()->fd;
        if ($usedFd === $selfFd) {
            return false;
        }
        foreach ($socket->getServer()->connections as $fd) {
            if ($fd === $usedFd) {
                return true;
            }
        }
        unset($socket->getServer()->info[$this->users[$id]]);
        unset($this->users[$id]);
        return false;
    }

    public function addUser(Socket $socket, array $info): void
    {
        $socket->getServer()->info[$socket->getFrame()->fd] = $info;
        $this->users[$info['id']] = $socket->getFrame()->fd;
    }

    public function getSelfInfo(Socket $socket): array
    {
        return $socket->getServer()->info[$socket->getFrame()->fd];
    }

    public function removeUser(Socket $socket): void
    {
        if (!$this->isGuest($socket)) {
            unset($this->users[$this->getSelfInfo($socket)['id']]);
            unset($socket->getServer()->info[$socket->getFrame()->fd]);
        }
    }

    /**
     * @return mixed
     */
    public function getTarget(Socket $socket)
    {
        $self = $socket->getFrame()->fd;
        foreach ($socket->getServer()->info as $fd => $info) {
            if ($fd !== $self) {
                if ($socket->isExists($fd)) {
                    return $fd;
                } else {
                    unset($socket->getServer()->info[$fd]);
                }
            }
        }
        return null;
    }

    public function sendTarget(Socket $socket, $data): void
    {
        $target = $this->getTarget($socket);
        if ($target === null) {
            $socket->emit([
                'type' => 'system',
                'data' => '还没有目标'
            ]);
            return;
        }

        if ($socket->isExists($target)) {
            $socket->emit([
                'type' => 'msg',
                'target' => 'target',
                'data' => $data
            ], $target);
        } else {
            $socket->emit([
                'type' => 'system',
                'data' => '对方不在线'
            ]);
        }
    }
}

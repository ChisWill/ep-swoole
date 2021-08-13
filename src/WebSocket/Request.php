<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\SocketIdentityRepositoryInterface;
use Yiisoft\Auth\IdentityInterface;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use LogicException;

final class Request
{
    private Server $server;
    private Frame $frame;
    private Nsp $nsp;
    private ?SocketIdentityRepositoryInterface $socketIdentityRepository;

    public function __construct(
        Server $server,
        Frame $frame,
        Nsp $nsp,
        SocketIdentityRepositoryInterface $socketIdentityRepository = null
    ) {
        $this->server = $server;
        $this->frame = $frame;
        $this->nsp = $nsp;
        $this->socketIdentityRepository = $socketIdentityRepository;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getFrame(): Frame
    {
        return $this->frame;
    }

    private bool $initIdentity = false;
    private ?IdentityInterface $identity = null;

    public function getIdentity(): ?IdentityInterface
    {
        if ($this->initIdentity === false) {
            $this->initIdentity = true;
            if ($this->socketIdentityRepository !== null && ($token = $this->socketIdentityRepository->findToken($this->frame->fd)) !== null) {
                $this->identity = $this->socketIdentityRepository->findIdentityByToken($token, $this->server->tokenType ?? null);
            }
        }
        return $this->identity;
    }

    public function isGuest(): bool
    {
        return $this->getIdentity() === null;
    }

    public function getId(): ?string
    {
        return $this->isGuest() ? null : $this->identity->getId();
    }

    public function getFd(string $id)
    {
    }

    public function join(string $room): self
    {
        $id = $this->getId();
        if ($id === null) {
            $this->unauthorize();
        }
        $this->nsp->join($id, $room);
        return $this;
    }

    public function leave(string $room): self
    {
        $id = $this->getId();
        if ($id === null) {
            $this->unauthorize();
        }
        $this->nsp->leave($id, $room);
        return $this;
    }

    public function isIn(string $room, string $id = null): bool
    {
        $id ??= $this->getId();
        if ($id === null) {
            return false;
        }
        return $this->nsp->exists($id, $room);
    }

    /**
     * @param mixed $data
     */
    public function broadcast(string $event, string $room, $data): self
    {
        $id = $this->getId();
        if ($id) {
            return $this;
        }

        if (!$this->nsp->exists($id, $room)) {
            return $this;
        }

        foreach ($this->nsp->connections($room) as $fd) {
            $fd = (int) $fd;
            if ($this->frame->fd !== $fd) {
                $this->emit($event, $data, $fd);
            }
        }
        return $this;
    }

    /**
     * @param mixed $data
     */
    public function emit(string $event, $data, int $fd = null): self
    {
        $this->server->push($fd ?? $this->frame->fd, $this->encode([$event, $data]));

        return $this;
    }

    public function isOnline(string $id = null): bool
    {
        $fd = $id === null ? $this->frame->fd : $this->socketIdentityRepository->findFd($id);
        if ($fd === null) {
            return false;
        }
        return $this->server->isEstablished($fd);
    }

    private string $route;

    public function getRoute(): string
    {
        $this->parseData();

        return $this->route;
    }

    /**
     * @var mixed
     */
    private $data = null;

    /**
     * @return mixed
     */
    public function getData()
    {
        $this->parseData();

        return $this->data;
    }

    private function parseData(): void
    {
        if ($this->data === null) {
            $frameData = json_decode($this->frame->data, true);
            if (is_array($frameData) && count($frameData) >= 2) {
                [$this->route, $this->data] = $frameData;
            } else {
                [$this->route, $this->data] = ['/', $this->frame->data];
            }
            $this->route = '/' . trim($this->route, '/');
        }
    }

    /**
     * @param mixed $data
     */
    private function encode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function unauthorize(): void
    {
        throw new LogicException('No definition found for ' . SocketIdentityRepositoryInterface::class . '.');
    }
}

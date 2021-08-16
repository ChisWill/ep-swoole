<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\WebSocketIdentityRepositoryInterface;
use Yiisoft\Auth\IdentityInterface;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use LogicException;

final class Request
{
    private Server $server;
    private Frame $frame;
    private Nsp $nsp;
    private ?WebSocketIdentityRepositoryInterface $socketIdentityRepository;

    public function __construct(
        Server $server,
        Frame $frame,
        Nsp $nsp,
        WebSocketIdentityRepositoryInterface $socketIdentityRepository = null
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
            if ($this->socketIdentityRepository !== null) {
                $this->identity = $this->socketIdentityRepository->findIdentity($this->frame->fd);
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

    public function getFd(string $id): ?int
    {
        return $this->socketIdentityRepository->findFd($id);
    }

    /**
     * @throws LogicException
     */
    public function join(string $room): self
    {
        if (($id = $this->getId()) === null) {
            $this->unauthorize();
        }

        $this->nsp->join($id, $room);

        return $this;
    }

    /**
     * @throws LogicException
     */
    public function leave(string $room): self
    {
        if (($id = $this->getId()) === null) {
            $this->unauthorize();
        }

        $this->nsp->leave($id, $room);

        return $this;
    }

    public function isIn(string $room, string $id = null): bool
    {
        $id ??= $this->getId();

        return $id === null ? false : $this->nsp->exists($id, $room);
    }

    /**
     * @param mixed $data
     */
    public function emit(string $event, $data): void
    {
        $this->server->push($this->frame->fd, $this->encode([$event, $data]));
    }

    /**
     * @param mixed $data
     * 
     * @throws LogicException
     */
    public function send(string $event, string $id, $data): void
    {
        if (($fd = $this->getFd($id)) !== null) {
            $this->server->push($fd, $this->encode([$event, $data]));
        }
    }

    /**
     * @param mixed $data
     * 
     * @throws LogicException
     */
    public function broadcast(string $event, string $room, $data): void
    {
        if (($id = $this->getId()) === null) {
            $this->unauthorize();
        }
        if (!$this->isIn($room)) {
            throw new LogicException('Not in room.');
        }

        foreach (array_diff($this->nsp->connections($room), [$id]) as $to) {
            $this->send($event, $to, $data);
        }
    }

    public function isOnline(string $id = null): bool
    {
        $fd = $id === null ? $this->frame->fd : $this->getFd($id);

        return $fd === null ? false : $this->server->isEstablished($fd);
    }

    private ?string $route = null;

    public function getRoute(): string
    {
        $this->parseData();

        return $this->route;
    }

    /**
     * @var mixed
     */
    private $data;

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
        if ($this->route === null) {
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
        if ($this->socketIdentityRepository === null) {
            throw new LogicException('No definition found for ' . WebSocketIdentityRepositoryInterface::class . '.');
        } else {
            throw new LogicException('No login.');
        }
    }
}

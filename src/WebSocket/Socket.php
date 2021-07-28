<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

final class Socket
{
    private Server $server;
    private Frame $frame;
    private Nsp $nsp;

    public function __construct(
        Server $server,
        Frame $frame,
        Nsp $nsp
    ) {
        $this->server = $server;
        $this->frame = $frame;
        $this->nsp = $nsp;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getFrame(): Frame
    {
        return $this->frame;
    }

    public function join(string $room): self
    {
        $this->nsp->join($this->frame->fd, $room);

        return $this;
    }

    public function to(string $room): self
    {
        $this->nsp->to($this->frame->fd, $room);

        return $this;
    }

    public function room(): ?string
    {
        return $this->nsp->find($this->frame->fd);
    }

    public function leave(string $room): self
    {
        $this->nsp->leave($this->frame->fd, $room);

        return $this;
    }

    /**
     * @param mixed $data
     */
    public function emit($data, int $fd = null): self
    {
        $this->server->push($fd ?? $this->frame->fd, $this->encode($data));

        return $this;
    }

    /**
     * @param mixed $data
     */
    public function broadcast($data): self
    {
        $room = $this->nsp->find($this->frame->fd);
        if ($room === null) {
            return $this;
        }

        foreach ($this->nsp->connections($room) as $fd) {
            $fd = (int) $fd;
            if ($this->frame->fd !== $fd) {
                $this->emit($data, $fd);
            }
        }
        return $this;
    }

    public function isExists(int $fd = null): bool
    {
        return $this->server->isEstablished($fd ?? $this->frame->fd);
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
}

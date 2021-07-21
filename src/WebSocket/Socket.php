<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

final class Socket
{
    private Server $server;
    private Frame $frame;

    public function __construct(Server $server, Frame $frame)
    {
        $this->server = $server;
        $this->frame = $frame;
    }

    /**
     * @param mixed $data
     */
    public function emit($data): void
    {
        $this->server->push($this->frame->fd, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function isExists(): bool
    {
        return $this->server->isEstablished($this->frame->fd);
    }

    private string $route;

    public function getRoute(): string
    {
        $this->parseData();

        return $this->route;
    }

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
            $frameData = json_decode($this->frame->data);
            if (is_array($frameData) && count($frameData) >= 2) {
                [$this->route, $this->data] = $frameData;
            } else {
                [$this->route, $this->data] = ['/', $frameData];
            }
        }
    }
}

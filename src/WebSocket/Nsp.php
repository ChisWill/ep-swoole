<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\NspAdapterInterface;

final class Nsp
{
    private NspAdapterInterface $adapter;

    public function __construct(NspAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function join(string $id, string $room): self
    {
        $this->adapter->add($room, $id);

        return $this;
    }

    public function exists(string $id, string $room): bool
    {
        return $this->adapter->exists($room, $id);
    }

    public function connections(string $room): array
    {
        return $this->adapter->values($room);
    }

    public function leave(string $id, string $room): self
    {
        $this->adapter->remove($room, $id);

        return $this;
    }
}

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

    /**
     * @param int|string $id
     */
    public function join($id, string $room): self
    {
        $this->adapter->add($room, (string) $id);

        return $this;
    }

    /**
     * @param int|string $id
     */
    public function exists($id, string $room): bool
    {
        return $this->adapter->exists($room, (string) $id);
    }

    public function connections(string $room): array
    {
        return $this->adapter->values($room);
    }

    /**
     * @param int|string $id
     */
    public function leave($id, string $room): self
    {
        $this->adapter->remove($room, (string) $id);

        return $this;
    }
}

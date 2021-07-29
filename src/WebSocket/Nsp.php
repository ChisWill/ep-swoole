<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\NspAdapterInterface;

final class Nsp
{
    private const PREFIX_ROOM = 'Ep-WS-Room-';

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
        $this->adapter->add(self::PREFIX_ROOM . $room, (string) $id);

        return $this;
    }

    /**
     * @param int|string $id
     */
    public function exists($id, string $room): bool
    {
        return $this->adapter->exists(self::PREFIX_ROOM . $room, (string) $id);
    }

    public function connections(string $room): array
    {
        return $this->adapter->values(self::PREFIX_ROOM . $room);
    }

    /**
     * @param int|string $id
     */
    public function leave($id, string $room): self
    {
        $this->adapter->remove(self::PREFIX_ROOM . $room, (string) $id);

        return $this;
    }
}

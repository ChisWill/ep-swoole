<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\NspAdapterInterface;

final class Nsp
{
    private const PREFIX_ROOM = 'Ep-WS-Room-';
    private const PREFIX_CURRENT = 'Ep-WS-Current-';

    private NspAdapterInterface $adapter;

    public function __construct(NspAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param int|string $id
     */
    public function join($id, string $name): self
    {
        $this->adapter->add(self::PREFIX_ROOM . $name, (string) $id);

        return $this;
    }

    /**
     * @param int|string $id
     */
    public function to($id, string $name): self
    {
        $this->join($id, $name);

        $this->adapter->set(self::PREFIX_CURRENT . $id, $name);

        return $this;
    }

    /**
     * @param int|string $id
     */
    public function find($id): ?string
    {
        return $this->adapter->get(self::PREFIX_CURRENT . $id);
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

        if ($room === $this->find($id)) {
            $this->adapter->set(self::PREFIX_CURRENT . $id, null);
        }

        return $this;
    }
}

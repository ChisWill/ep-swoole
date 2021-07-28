<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket\NspAdapter;

use Ep\Swoole\Contract\NspAdapterInterface;

final class ArrayAdapter implements NspAdapterInterface
{
    private array $array = [];

    /**
     * {@inheritDoc}
     */
    public function add(string $key, string $value): void
    {
        $this->array[$key][$value] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function values(string $key): array
    {
        return array_values($this->array[$key] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key, string $value): void
    {
        unset($this->array[$key][$value]);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, ?string $value): void
    {
        if ($value === null) {
            unset($this->array[$key]);
        } else {
            $this->array[$key] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        return $this->array[$key] ?? null;
    }
}

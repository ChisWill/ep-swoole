<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket\NspAdapter;

use Ep\Swoole\Contract\NspAdapterInterface;
use Yiisoft\Db\Redis\Connection;

final class RedisAdapter implements NspAdapterInterface
{
    private Connection $redis;

    public function __construct(Connection $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $key, string $value): void
    {
        $this->redis->sadd($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function values(string $key): array
    {
        return $this->redis->smembers($key);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key, string $value): void
    {
        $this->redis->srem($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, ?string $value): void
    {
        if ($value === null) {
            $this->redis->del($key);
        } else {
            $this->redis->set($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        return $this->redis->get($key);
    }
}

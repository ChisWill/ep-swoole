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
    public function exists(string $key, string $value): bool
    {
        return $this->redis->sismember($key, $value) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key, string $value): void
    {
        $this->redis->srem($key, $value);
    }
}

<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket\NspAdapter;

use Ep\Swoole\Contract\NspAdapterInterface;
use Yiisoft\Db\Redis\Connection;

final class RedisAdapter implements NspAdapterInterface
{
    private const PREFIX = 'Ep-WS-Room-';

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
        $this->redis->sadd(self::PREFIX . $key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function values(string $key): array
    {
        return $this->redis->smembers(self::PREFIX . $key);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $key, string $value): bool
    {
        return $this->redis->sismember(self::PREFIX . $key, $value) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key, string $value): void
    {
        $this->redis->srem(self::PREFIX . $key, $value);
    }
}

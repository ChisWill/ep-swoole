<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Swoole\WebSocket\Authentication;
use Ep\Annotation\Inject;
use Yiisoft\Db\Redis\Connection;

class AuthMethod extends Authentication
{
    /**
     * @Inject
     */
    private Connection $redis;

    protected function bind(int $fd, string $id): void
    {
        $this->redis->hset('websocket-user-fd', $fd, $id);
        $oldFd = $this->redis->hget('websocket-user-id', $id);
        $this->redis->hset('websocket-user-id', $id, $fd);
        if ($oldFd) {
            $this->redis->hdel('websocket-user-fd', $oldFd);
        }
    }
}

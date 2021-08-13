<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Yiisoft\Db\Redis\Connection;

final class SocketFdRepository
{
    /**
     * @Inject
     */
    private Connection $redis;

    public function update(int $fd, ?string $id, ?string $token): void
    {
        $this->redis->hset('websocket-user-fd', $fd, $token);
        $oldFd = $this->redis->hget('websocket-user-id', $id);
        $this->redis->hset('websocket-user-id', $id, $fd);
        if ($oldFd) {
            $this->redis->hdel('websocket-user-fd', $oldFd);
        }
    }
}

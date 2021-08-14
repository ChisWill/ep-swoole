<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Annotation\Inject;
use Ep\Swoole\Contract\SocketIdentityRepositoryInterface;
use Ep\Tests\App\Model\Student;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Db\Redis\Connection;

final class IdentityRepository implements SocketIdentityRepositoryInterface
{
    /**
     * @Inject
     */
    private Connection $redis;

    public function findFd(string $id): ?int
    {
        return (int) $this->redis->hget('websocket-user-id', $id) ?: null;
    }

    public function findToken(int $fd): ?string
    {
        return $this->redis->hget('websocket-user-fd', $fd) ?: null;
    }

    public function findIdentityByToken(string $token, ?string $type = null): ?IdentityInterface
    {
        $id = trim(json_decode(base64_decode($token)), 'A');
        return Student::find(Ep::getDb('sqlite'))
            ->where([
                'id' => $id,
                'class_id' => $type
            ])
            ->one();
    }
}

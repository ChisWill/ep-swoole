<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

interface WebSocketIdentityRepositoryInterface extends IdentityWithTokenRepositoryInterface
{
    public function findIdentity(int $fd): ?IdentityInterface;

    public function findFd(string $id): ?int;
}

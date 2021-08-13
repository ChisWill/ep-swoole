<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

interface SocketIdentityRepositoryInterface extends IdentityWithTokenRepositoryInterface
{
    public function findToken(int $fd): ?string;

    public function findFd(string $id): ?int;
}

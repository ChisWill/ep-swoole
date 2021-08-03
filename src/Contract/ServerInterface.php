<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Swoole\Server;

interface ServerInterface
{
    public function start(): void;

    public function set(array $settings): void;

    public function getServer(): Server;
}

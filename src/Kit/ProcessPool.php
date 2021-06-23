<?php

declare(strict_types=1);

namespace Ep\Swoole\Kit;

use Swoole\Process\Pool;

final class ProcessPool
{
    public function simpleDo(array $data, callable $callback): void
    {
        $pool = new Pool(count($data));
        $pool->on('workerStart', static fn (Pool $pool, int $workerId) => $callback($data[$workerId], $pool->getProcess($workerId), $pool));
        $pool->start();
    }
}

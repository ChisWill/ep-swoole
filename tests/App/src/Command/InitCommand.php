<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Console\Command;
use Exception;
use Swoole\Coroutine;
use Swoole\Coroutine\Barrier;
use Swoole\Process;
use Swoole\Process\Pool;

class InitCommand extends Command
{
    public function indexAction()
    {
        $message = 'Welcome Basic';

        return $this->success($message);
    }

    public function logAction()
    {
        Ep::getLogger()->info('log info', ['act' => self::class]);

        return $this->success();
    }
}

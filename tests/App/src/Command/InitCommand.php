<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Console\Command;
use Ep\Swoole\Kit\ProcessPool;
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

    public function taskAction()
    {
        $pool = new ProcessPool();
        $tasks = [
            [
                'command' => self::class,
                'action' => 'indexAction',
                'delay' => 1
            ],
            [
                'command' => self::class,
                'action' => 'logAction',
                'delay' => 2
            ],
        ];
        $pool->simpleDo($tasks, function ($task, Process $process, Pool $pool) {
            $process = new Process(function () {
                echo 1;
            });
            $process->start();
            sleep($task['delay']);
        });
        return $this->success();
    }
}

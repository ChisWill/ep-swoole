<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Console\Application as ConsoleApplication;
use Ep\Swoole\Command\StartCommand;
use Ep\Swoole\Command\StopCommand;
use Psr\Container\ContainerInterface;

final class Application
{
    private array $commands = [
        StartCommand::class,
        StopCommand::class
    ];

    private ConsoleApplication $application;
    private ContainerInterface $container;

    public function __construct(
        ConsoleApplication $application,
        ContainerInterface $container
    ) {
        $this->application = $application;
        $this->container = $container;
    }

    public function run(array $swooleConfig): void
    {
        foreach ($this->commands as $class) {
            $command = $this->container->get($class);
            if ($command instanceof StartCommand) {
                $command->setConfig($swooleConfig);
            }
            $this->application->add($command);
        }

        $this->application->run();
    }
}

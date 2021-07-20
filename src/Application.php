<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Console\Application as ConsoleApplication;
use Ep\Swoole\Command\StartCommand;
use Ep\Swoole\Command\StopCommand;
use Psr\Container\ContainerInterface;

final class Application
{
    private const DEFAULT_COMMANDS = [
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

    private array $commands = [];

    public function withCommands(array $commands): self
    {
        $new = clone $this;
        $new->commands = $commands;
        return $new;
    }

    public function run(array $swooleConfig): int
    {
        foreach (array_merge(self::DEFAULT_COMMANDS, $this->commands) as $class) {
            $command = $this->container->get($class);
            if ($command instanceof StartCommand) {
                $command->setConfig($swooleConfig);
            }
            $this->application->add($command);
        }

        return $this->application->run();
    }
}

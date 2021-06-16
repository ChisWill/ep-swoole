<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Swoole\Command\StartCommand;
use Ep\Swoole\Command\StopCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application
{
    private array $commands = [
        StartCommand::class,
        StopCommand::class
    ];

    private SymfonyApplication $symfonyApplication;
    private ContainerInterface $container;

    public function __construct(
        SymfonyApplication $symfonyApplication,
        ContainerInterface $container
    ) {
        $this->symfonyApplication = $symfonyApplication;
        $this->container = $container;
    }

    public function run(array $swooleConfig): void
    {
        foreach ($this->commands as $class) {
            $command = $this->container->get($class);
            if ($command instanceof StartCommand) {
                $command->setConfig($swooleConfig);
            }
            $this->symfonyApplication->add($command);
        }

        $this->symfonyApplication->run();
    }
}

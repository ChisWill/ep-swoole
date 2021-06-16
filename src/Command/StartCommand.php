<?php

declare(strict_types=1);

namespace Ep\Swoole\Command;

use Ep\Contract\InjectorInterface;
use Ep\Swoole\Config;
use Ep\Swoole\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class StartCommand extends Command
{
    private InjectorInterface $injector;

    public function __construct(InjectorInterface $injector)
    {
        parent::__construct('start');

        $this->injector = $injector;
    }

    protected function configure()
    {
        $this
            ->setDescription('Run swoole server')
            ->setDefinition([
                new InputOption('daemonize', 'd', InputOption::VALUE_NONE, 'Server start in <comment>DAEMON</> mode')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $settings['daemonize'] = $input->getOption('daemonize');

        $this->injector
            ->make(Server::class, [
                'config' => $this->config,
                'settings' => $settings
            ])
            ->run();

        return self::SUCCESS;
    }

    private Config $config;

    public function setConfig(array $config): void
    {
        $this->config = new Config($config);
    }
}

<?php

declare(strict_types=1);

namespace Ep\Swoole\Command;

use Ep\Swoole\Kit\SystemKit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReloadCommand extends Command
{
    private SystemKit $systemKit;

    public function __construct(SystemKit $systemKit)
    {
        parent::__construct('reload');

        $this->systemKit = $systemKit;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Reload swoole server');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = self::SUCCESS;
        if (!file_exists($pidFile = $this->systemKit->getPidFile())) {
            return $exitCode;
        }

        passthru('kill -USR1 ' . file_get_contents($pidFile), $exitCode);

        return $exitCode;
    }
}

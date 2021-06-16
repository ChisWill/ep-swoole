<?php

declare(strict_types=1);

namespace Ep\Swoole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StopCommand extends Command
{
    public function __construct()
    {
        parent::__construct('stop');
    }

    protected function configure()
    {
        $this->setDescription('Stop swoole server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}

#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ep\Swoole\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$exitCode = Ep::init(dirname(__DIR__))
    ->get(Application::class)
    ->run(import('swoole'));

exit($exitCode);

#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ep\Swoole\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));

Ep::getDi()
    ->get(Application::class)
    ->run(require(dirname(__DIR__) . '/config/swoole.php'));

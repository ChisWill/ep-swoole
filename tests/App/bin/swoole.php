#!/usr/bin/env php
<?php

use Ep\Swoole\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));

Ep::getDi()
    ->get(Application::class)
    ->set(require(dirname(__DIR__) . '/config/swoole.php'))
    ->run();

#!/usr/bin/env php
<?php

use Ep\Swoole\Application;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));

Ep::getDi()
    ->get(Application::class)
    ->setConfig(require(dirname(__DIR__) . '/config/swoole.php'))
    ->run();

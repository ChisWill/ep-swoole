<?php

declare(strict_types=1);

return [
    'rootNamespace' => 'Ep\Tests\App',
    'rootPath' => dirname(__DIR__, 1),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'baseUrl' => '/',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FA893E11FWE2340C6A68663217A181',
    'di' => require('di.php'),
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => require('params.php')
];

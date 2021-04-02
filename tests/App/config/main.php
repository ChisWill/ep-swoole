<?php

declare(strict_types=1);

return [
    'appNamespace' => 'Ep\Tests\App',
    'rootPath' => dirname(__DIR__, 1),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'baseUrl' => '/',
    'mysqlDsn' => 'mysql:host=127.0.0.1;dbname=test',
    'mysqlUsername' => 'root',
    'mysqlPassword' => '',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FA893E11FWE2340C6A68663217A181',
    'di' => require('di.php'),
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => require('params.php')
];

<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Swoole\Contract\NspAdapterInterface;
use Ep\Swoole\Contract\WebsocketErrorRendererInterface;
use Ep\Swoole\WebSocket\NspAdapter\ArrayAdapter;
use Ep\Swoole\WebSocket\NspAdapter\RedisAdapter;
use Ep\Tests\App\Component\WebsocketErrorRenderer;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Db\Sqlite\Connection as SqliteConnection;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;

return static fn (Config $config): array => [
    FileTarget::class => static function (Aliases $aliases) use ($config): FileTarget {
        $fileTarget = new FileTarget($aliases->get($config->runtimeDir . '/logs/' . date('Y-m-d') . '.log'), new FileRotator());
        $fileTarget->setExportInterval(1);
        return $fileTarget;
    },
    LoggerInterface::class => static function (FileTarget $fileTarget): LoggerInterface {
        $logger = new Logger([$fileTarget]);
        $logger->setFlushInterval(1);
        return $logger;
    },
    NspAdapterInterface::class => ArrayAdapter::class,
    WebsocketErrorRendererInterface::class => WebsocketErrorRenderer::class,
    // Sqlite
    'sqlite' => [
        'class' => SqliteConnection::class,
        '__construct()' => ['sqlite:' . dirname(__FILE__) . '/ep.sqlite'],
    ],
    // Redis
    RedisConnection::class => [
        'class' => RedisConnection::class,
        'hostname()' => [$config->params['db']['redis']['hostname']],
        'database()' => [$config->params['db']['redis']['database']],
        'password()' => [$config->params['db']['redis']['password']],
        'port()'     => [$config->params['db']['redis']['port']]
    ],
    // Mysql
    Connection::class => [
        'class' => MysqlConnection::class,
        '__construct()' => [$config->params['db']['mysql']['dsn']],
        'setUsername()' => [$config->params['db']['mysql']['username']],
        'setPassword()' => [$config->params['db']['mysql']['password']]
    ]
];

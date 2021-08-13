<?php

declare(strict_types=1);

use Ep\Swoole\Server;
use Ep\Swoole\SwooleEvent;
use Ep\Tests\App\Component\WebSocketEvent;

return [
    'host' => '0.0.0.0',
    'port' => 9501,
    'type' => Server::WEBSOCKET,
    'settings' => [
        'worker_num' => 1,
        'max_wait_time' => 10,
        'http_compression' => false
    ],
    'events' => [
        SwooleEvent::ON_OPEN => [WebSocketEvent::class, 'onOpen'],
        SwooleEvent::ON_WORKER_START => [WebSocketEvent::class, 'onWorkerStart'],
        SwooleEvent::ON_WORKER_STOP => [WebSocketEvent::class, 'onWorkerStop'],
        SwooleEvent::ON_CLOSE => [WebSocketEvent::class, 'onClose']
    ],
    'servers' => [
        [
            'port' => 9502,
            'settings' => [
                'open_http_protocol' => true
            ]
        ]
    ]
];

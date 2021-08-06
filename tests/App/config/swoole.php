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
        'worker_num' => 4,
        'max_wait_time' => 10,
        'http_compression' => false
    ],
    'events' => [
        SwooleEvent::ON_OPEN => [WebSocketEvent::class, 'onOpen']
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

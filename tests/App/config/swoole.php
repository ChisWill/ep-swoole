<?php

declare(strict_types=1);

use Ep\Swoole\SwooleServer;

return [
    'host' => '0.0.0.0',
    'port' => 9501,
    'type' => SwooleServer::WEBSOCKET,
    'settings' => [],
    'events' => [],
    'servers' => [
        [
            'port' => 9502,
            'settings' => [
                'open_http_protocol' => true
            ]
        ]
    ]
];

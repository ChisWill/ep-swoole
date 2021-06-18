<?php

declare(strict_types=1);

use Ep\Swoole\Server;

return [
    'host' => '0.0.0.0',
    'port' => 9501,
    'type' => Server::WEBSOCKET,
    'settings' => [
        'worker_num' => 2
    ],
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

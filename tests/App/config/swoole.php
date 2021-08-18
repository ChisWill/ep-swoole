<?php

declare(strict_types=1);

use Ep\Swoole\ServerFactory;
use Ep\Tests\App\Component\WebSocketEvent;
use Swoole\Constant;

return [
    'host' => '0.0.0.0',
    'port' => 9501,
    'type' => ServerFactory::WEBSOCKET,
    'settings' => [
        'worker_num' => 1,
        'task_worker_num' => 1,
        'max_wait_time' => 10,
        'http_compression' => false
    ],
    'events' => [
        Constant::EVENT_OPEN => [WebSocketEvent::class, 'onOpen'],
        Constant::EVENT_WORKER_START => [WebSocketEvent::class, 'onWorkerStart'],
        Constant::EVENT_WORKER_STOP => [WebSocketEvent::class, 'onWorkerStop'],
        Constant::EVENT_CLOSE => [WebSocketEvent::class, 'onClose'],
        Constant::EVENT_TASK => [WebSocketEvent::class, 'onTask'],
        Constant::EVENT_FINISH => [WebSocketEvent::class, 'onFinish']
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

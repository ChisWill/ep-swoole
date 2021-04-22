<?php

declare(strict_types=1);

use Ep\Base\Config;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
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
];

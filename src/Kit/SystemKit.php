<?php

declare(strict_types=1);

namespace Ep\Swoole\Kit;

use Ep\Base\Config;
use Yiisoft\Aliases\Aliases;

final class SystemKit
{
    private Config $config;
    private Aliases $aliases;

    public function __construct(
        Config $config,
        Aliases $aliases
    ) {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    public function getPidFile(): string
    {
        return $this->aliases->get($this->config->runtimeDir . '/swoole.pid');
    }
}

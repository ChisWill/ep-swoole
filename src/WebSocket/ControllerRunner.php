<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Base\ControllerRunner as BaseControllerRunner;

final class ControllerRunner extends BaseControllerRunner
{
    private string $controllerSuffix;

    public function withControllerSuffix(string $controllerSuffix): self
    {
        $new = clone $this;
        $new->controllerSuffix = $controllerSuffix;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerSuffix(): string
    {
        return $this->controllerSuffix;
    }
}

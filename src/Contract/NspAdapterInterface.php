<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

interface NspAdapterInterface
{
    public function add(string $key, string $value): void;

    public function values(string $key): array;

    public function exists(string $key, string $value): bool;

    public function remove(string $key, string $value): void;
}

<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

interface NspAdapterInterface
{
    public function add(string $key, string $value): void;

    public function values(string $key): array;

    public function remove(string $key, string $value): void;

    public function set(string $key, ?string $value): void;

    public function get(string $key): ?string;
}

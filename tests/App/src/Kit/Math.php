<?php

declare(strict_types=1);

namespace Ep\Tests\App\Kit;

final class Math
{
    public static function formatByte(int $size): string
    {
        if ($size === 0) {
            return '0B';
        }
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
    }
}

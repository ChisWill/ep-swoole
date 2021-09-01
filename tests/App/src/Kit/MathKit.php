<?php

declare(strict_types=1);

namespace Ep\Tests\App\Kit;

use Ep\Helper\Math;

final class MathKit
{
    public static function formatByte(int $size): string
    {
        return Math::convertUnit($size, ['B', 'KB', 'MB', 'GB', 'TB', 'PB'], 1024, 3);
    }
}

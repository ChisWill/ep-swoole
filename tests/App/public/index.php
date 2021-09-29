<?php

declare(strict_types=1);

use Ep\Web\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(dirname(__DIR__))->get(Application::class)->run();

<?php

use Ep\Tests\App\Controller\DemoController;

return [
    DemoController::class => [fn (DemoController $event) => $event->testAction()]
];

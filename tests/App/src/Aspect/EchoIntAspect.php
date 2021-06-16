<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Psr\Http\Message\ResponseInterface;

class EchoIntAspect implements AspectInterface
{
    public function process(HandlerInterface $handler)
    {
        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write('<br>who:int-2<br>');
        return $response;
    }
}

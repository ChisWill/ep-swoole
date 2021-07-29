<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Annotation\Aspect;
use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Nsp;
use Ep\Tests\App\Aspect\EchoIntAspect;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Middleware\TimeMiddleware;
use Ep\Tests\App\Service\TestService;
use Ep\Web\ErrorHandler;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\Db\Redis\Connection;

class TestController extends Controller
{
    public string $title = 'Test';

    /**
     * @Inject
     */
    private TestService $service;

    public function __construct()
    {
        $this->setMiddlewares([
            TimeMiddleware::class
        ]);
    }

    public function indexAction(ServerRequestInterface $req)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function sleepAction()
    {
        sleep(5);

        return 'ok';
    }

    public function stringAction()
    {
        return 'test string';
    }

    public function arrayAction()
    {
        return [
            'state' => 1,
            'data' => [
                'msg' => 'ok'
            ]
        ];
    }

    public function nspAction(Nsp $nsp, Connection $redis)
    {
        $nsp->join('1', 'car');
        $nsp->join('2', 'car');
        $nsp->join('3', 'car');

        $b1 = $nsp->exists('1', 'car');
        $b2 = $nsp->exists('2', 'bike');

        $nsp->leave('1', 'car');

        return $this->json([
            'connections1' => $nsp->connections('bike'),
            'connections2' => $nsp->connections('car'),
            'exists1' => $b1,
            'exists2' => $b2
        ]);
    }

    /**
     * @Aspect(EchoIntAspect::class)
     */
    public function aspectAction()
    {
        return $this->string($this->service->getRandomString());
    }

    public function tAction()
    {
        return $this->string();
    }

    public function errorAction(ServerRequestInterface $request)
    {
        $handler = Ep::getDi()->get(ErrorHandler::class);

        return $this->string($handler->renderException(
            new RuntimeException(
                '我错了',
                500,
                new RuntimeException(
                    "我又错啦",
                    600,
                    new RuntimeException(
                        "我怎么总错",
                        700
                    )
                ),
            ),
            $request
        ));
    }
}

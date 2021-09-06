<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Annotation\Aspect;
use Ep\Annotation\Inject;
use Ep\Swoole\WebSocket\Nsp;
use Ep\Tests\App\Aspect\EchoIntAspect;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Kit\MathKit;
use Ep\Tests\App\Middleware\TimeMiddleware;
use Ep\Tests\App\Service\TestService;
use Ep\Tests\Support\Normal\Eagle;
use Ep\Web\ErrorRenderer;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Factory\Factory;

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
            // TimeMiddleware::class
        ]);
    }

    public function indexAction(ServerRequestInterface $req)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function viewAction()
    {
        return $this->render('view');
    }

    public function sleepAction()
    {
        sleep(5);

        return $this->string('ok');
    }

    public function stringAction()
    {
        return $this->string('test string');
    }

    public function arrayAction()
    {
        return $this->json([
            'state' => 1,
            'data' => [
                'msg' => 'ok'
            ]
        ]);
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

    public function tAction(ServerRequestInterface $request)
    {
        $start = memory_get_usage();
        $count = mt_rand(100, 200);
        for ($i = $count; $i--;) {
            $this->service->handleRequest();
        }
        $middle = memory_get_usage();
        for ($i = $count; $i--;) {
            $this->service->handleRequest();
        }
        $end = memory_get_usage();
        tt(MathKit::formatByte($middle - $start), MathKit::formatByte($end - $middle));
    }

    public function factoryAction()
    {
        $difinition = [
            'class' => Eagle::class,
            '__construct()' => ['Slap']
        ];

        $factory = new Factory(Ep::getDi());
        $eagle = $factory->create($difinition);

        tt($eagle);
    }

    public function errorAction(ServerRequestInterface $request)
    {
        $handler = Ep::getDi()->get(ErrorRenderer::class);

        return $this->string($handler->render(
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

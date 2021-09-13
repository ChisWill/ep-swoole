<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep;
use Ep\Annotation\Inject;
use Ep\Base\ControllerLoader;
use Ep\Contract\NotFoundHandlerInterface;
use Ep\Helper\Str;
use Ep\Helper\Curl;
use Ep\Helper\Url;
use Ep\Tests\App\Controller\DemoController;
use Ep\Tests\App\Controller\TestController;
use Ep\Web\ControllerRunner;
use Ep\Web\Middleware\RouteMiddleware;
use Ep\Web\RequestHandlerFactory;
use Ep\Web\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Swoole\Process;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;

class TestService
{
    /**
     * @Inject
     */
    private ContainerInterface $container;

    public function getRandomString(): string
    {
        return Str::random();
    }

    /**
     * @Inject
     */
    private ServerRequestFactory $serverRequestFactory;
    /**
     * @Inject
     */
    private RequestHandlerFactory $requestHandlerFactory;
    /**
     * @Inject
     */
    private NotFoundHandlerInterface $notFoundHandler;
    private array $middlewares = [
        RouteMiddleware::class
    ];

    public function curlRequest(): void
    {
        $baseUrl = 'http://localhost:9501/demo/getUser';
        $batch = 10;
        $urls = [];
        for ($i = 1; $i <= $batch; $i++) {
            $urls[] = $baseUrl . '?id=' . $i;
        }

        $result = Curl::getMulti($urls, '', [], $batch);
        tt($result);
    }

    public function handleRequest(string $action): void
    {
        $_SERVER = ['REQUEST_METHOD' => 'GET'];
        $request = new ServerRequest($this->serverRequestFactory->createFromGlobals());
        $uri = $request
            ->getUri()
            ->withPath('/demo/' . $action);
        $request = $request->withUri($uri);

        // $runner = $this->container->get(ControllerRunner::class);
        // $loader = Ep::getInjector()->make(ControllerLoader::class, [
        //     'suffix' => 'Controller'
        // ]);
        // $loader = $loader->parse($uri->getPath());

        // $runner->runLoader($loader, $request);
        // $runner->runAll(null, new DemoController, 'pdoAction', $request);
        // Ep::getInjector()->call($loader->getController(), $loader->getAction(), [$request]);

        $response = $this->requestHandlerFactory
            ->wrap($this->middlewares, $this->notFoundHandler)
            ->handle($request);

        if ((int) ($this->decode($response)['errno'] ?? -1) !== 0) {
            throw new RuntimeException('handle error');
        }
        // (new SapiEmitter())->emit($response);die;
    }

    private function decode(ResponseInterface $response): array
    {
        $body = $response->getBody();
        $body->rewind();
        $content = '';
        while (!$body->eof()) {
            $content .= $body->read(9999999);
        }
        return json_decode($content, true) ?: [];
    }
}

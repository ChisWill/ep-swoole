<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Base\Application as BaseApplication;
use Ep\Console\Application as Console;
use Ep\Contract\ConsoleRequestInterface;
use Yiisoft\Injector\Injector;

final class Application extends BaseApplication
{
    private Console $console;
    private Injector $injector;

    public function __construct(Console $console, Injector $injector)
    {
        $this->console = $console;
        $this->injector = $injector;
    }

    private Config $config;

    public function setConfig(array $config): self
    {
        $this->config = new Config($config);
        return $this;
    }

    public function createRequest(): ConsoleRequestInterface
    {
        return $this->console->createRequest();
    }

    public function register($request): void
    {
        $this->console->register($request);
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    public function handleRequest($request): void
    {
        $command = $this->parseRoute($request->getRoute());
        $settings = $this->parseParams($request->getParams());
        switch ($command) {
            case '':
            case 'start':
                $server = $this->injector->make(SwooleServer::class, [
                    'config' => $this->config,
                    'settings' => $settings
                ]);
                $server->run();
                break;
            case 'stop':
                break;
            case 'reload':
                break;
            default:
                echo <<<HELP
Usage: php yourfile <command> [mode]
Commands: start, stop, reload
Modes: -d
HELP;
        }
    }

    public function send($request, $response): void
    {
        exit(0);
    }

    private function parseRoute(string $route): string
    {
        return trim($route, '/');
    }

    private function parseParams(array $params): array
    {
        $settings['daemonize'] = ($params['d'] ?? false) === true;
        return $settings;
    }
}

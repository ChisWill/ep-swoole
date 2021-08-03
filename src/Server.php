<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Contract\InjectorInterface;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\Tcp\Server as TcpServer;
use Ep\Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Runtime;
use InvalidArgumentException;

final class Server
{
    public const HTTP = 1;
    public const WEBSOCKET = 2;
    public const TCP = 3;

    private InjectorInterface $injector;
    private Config $config;
    private array $settings;

    public function __construct(InjectorInterface $injector, Config $config, array $settings)
    {
        $this->injector = $injector;
        $this->config = $config;
        $this->settings = $settings + $this->config->settings;
    }

    public function run(): void
    {
        Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);

        $mainServer = $this->createMainServer();

        $mainServer->set($this->settings);

        $mainServer->start();
    }

    private function createMainServer(): ServerInterface
    {
        switch ($this->config->type) {
            case self::HTTP:
                return $this->injector->make(HttpServer::class, [$this->config]);
            case self::WEBSOCKET:
                return $this->injector->make(WebSocketServer::class, [$this->config]);
            case self::TCP:
                return $this->injector->make(TcpServer::class, [$this->config]);
            default:
                throw new InvalidArgumentException('The "type" configuration is invalid.');
        }
    }
}

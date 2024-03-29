<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Contract\InjectorInterface;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\Kit\SystemKit;
use Ep\Swoole\Tcp\Server as TcpServer;
use Ep\Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Coroutine;
use InvalidArgumentException;

final class ServerFactory
{
    public const TCP = 1;
    public const HTTP = 2;
    public const WEBSOCKET = 3;

    private InjectorInterface $injector;
    private SystemKit $systemKit;
    private Config $config;
    private array $settings;

    public function __construct(
        InjectorInterface $injector,
        SystemKit $systemKit,
        Config $config,
        array $settings
    ) {
        $this->injector = $injector;
        $this->systemKit = $systemKit;
        $this->config = $config;
        $this->settings = $settings + $this->config->settings;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(): ServerInterface
    {
        Coroutine::set(array_merge([
            'hook_flags' => SWOOLE_HOOK_ALL
        ], $this->config->coroutineOptions));

        $mainServer = $this->createMainServer();

        $mainServer->getServer()->set($this->getDefaultSettings() + $this->settings);

        return $mainServer;
    }

    private function createMainServer(): ServerInterface
    {
        switch ($this->config->type) {
            case self::TCP:
                $class = TcpServer::class;
                break;
            case self::HTTP:
                $class = HttpServer::class;
                break;
            case self::WEBSOCKET:
                $class = WebSocketServer::class;
                break;
            default:
                throw new InvalidArgumentException('The "type" configuration is invalid.');
        }
        return $this->injector->make($class, [$this->config]);
    }

    private function getDefaultSettings(): array
    {
        return [
            'pid_file' => $this->systemKit->getPidFile(),
            'reload_async' => true
        ];
    }
}

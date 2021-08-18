<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep;
use Ep\Base\ErrorRenderer;
use Ep\Swoole\Config;
use Swoole\Server;
use Swoole\Server\Port;
use InvalidArgumentException;
use Throwable;

trait ServerTrait
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    private ?Server $server = null;

    public function getServer(): Server
    {
        if ($this->server === null) {
            $class = $this->getServerClass();

            $this->server = new $class(
                $this->config->host,
                $this->config->port,
                $this->config->mode,
                $this->config->sockType
            );

            $this->bindEvent($this->server, $this->config->events);
            $this->createSubServer($this->server, $this->config->servers);
        }

        return $this->server;
    }

    public function start(): void
    {
        $this->onRequest();

        $this->getServer()->start();
    }

    /**
     * @param Server|Port $server
     */
    private function bindEvent($server, array $events): void
    {
        $di = Ep::getDi();
        foreach ($events as $event => $callback) {
            if (!is_callable($callback) && !is_array($callback)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration is an array of string-callback pairs.");
            }
            if (is_array($callback) && is_string(current($callback))) {
                $callback = [$di->get(array_shift($callback)), array_shift($callback)];
            }

            $server->on($event, static function () use ($di, $event, $callback): void {
                try {
                    call_user_func_array($callback, func_get_args());
                } catch (Throwable $t) {
                    if ($di->has(EventErrorRendererInterface::class)) {
                        $di->get(EventErrorRendererInterface::class)->render($t, $event, func_get_args());
                    } else {
                        echo $di->get(ErrorRenderer::class)->render($t, $event);
                    }
                }
            });
        }
    }

    private function createSubServer(Server $server, array $servers): void
    {
        foreach ($servers as $config) {
            if (!isset($config['port'])) {
                throw new InvalidArgumentException("The \"servers[port]\" configuration is required.");
            }

            $config['host'] ??= '0.0.0.0';
            $port = $server->listen(
                $config['host'],
                $config['port'],
                $config['sockType'] ?? SWOOLE_SOCK_TCP,
            );
            if (!$port instanceof Port) {
                throw new InvalidArgumentException("Failed to listen server port [{$config['host']}:{$config['port']}]");
            }
            $port->set($config['settings'] ?? []);

            $this->bindEvent($port, $config['events'] ?? []);
        }
    }

    abstract protected function getServerClass(): string;

    abstract protected function onRequest(): void;
}

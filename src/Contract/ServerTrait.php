<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep;
use Ep\Swoole\Config;
use Ep\Swoole\SwooleEvent;
use Swoole\Server;
use Swoole\Server\Port;
use InvalidArgumentException;

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
            $this->listenServer($this->server, $this->config->servers);
        }

        return $this->server;
    }

    public function start(): void
    {
        $this->onRequest();

        $this->getServer()->start();
    }

    public function set(array $settings): void
    {
        $this->getServer()->set($settings);
    }

    /**
     * @param Server|Port $server
     */
    private function bindEvent($server, array $events): void
    {
        foreach ($events as $event => $callback) {
            if (!SwooleEvent::isSwooleEvent($event)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration must have Swoole Event as the key of the array.");
            }
            if (!is_callable($callback) && !is_array($callback)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration is an array of string-callback pairs.");
            }
            if (is_array($callback) && is_string(current($callback))) {
                $callback = [Ep::getDi()->get(array_shift($callback)), array_shift($callback)];
            }
            $server->on($event, $callback);
        }
    }

    private function listenServer(Server $server, array $servers): void
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
            if ($port instanceof Port) {
                $port->set($config['settings'] ?? []);
                $this->bindEvent($port, $config['events'] ?? []);
            } else {
                throw new InvalidArgumentException("Failed to listen server port [{$config['host']}:{$config['port']}]");
            }
        }
    }

    abstract protected function getServerClass(): string;

    abstract protected function onRequest(): void;
}

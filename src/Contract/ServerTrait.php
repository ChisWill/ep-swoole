<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

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

    private Server $server;

    public function init(): void
    {
        $class = $this->getServerClass();

        $this->server = new $class(
            $this->config->host,
            $this->config->port,
            $this->config->mode,
            $this->config->sockType
        );

        $this->onEvents($this->server, $this->config->events);

        $this->listenServers($this->server, $this->config->servers);
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function start(array $settings): void
    {
        $this->onRequest();

        $this->server->set($settings);

        $this->server->start();
    }

    /**
     * @param Server|Port $port
     */
    private function onEvents($port, array $events): void
    {
        foreach ($events as $event => $callback) {
            if (!SwooleEvent::isSwooleEvent($event)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration must have Swoole Event as the key of the array.");
            }
            if (!is_callable($callback)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration is an array of string-callback pairs.");
            }
            $port->on($event, $callback);
        }
    }

    /** 
     * @var Port[] $ports 
     */
    private array $ports = [];

    private function listenServers(Server $server, array $servers): void
    {
        foreach ($servers as $config) {
            if (!isset($config['port'])) {
                throw new InvalidArgumentException("The \"servers[port]\" configuration is required.");
            }
            $config['host'] ??= '0.0.0.0';
            $port = $server->listen(
                $config['host'],
                $config['port'],
                $config['socketType'] ?? SWOOLE_SOCK_TCP,
            );
            if ($port instanceof Port) {
                $port->set($config['settings'] ?? []);
                $this->onEvents($port, $config['events'] ?? []);
                $this->ports[] = $port;
            } else {
                throw new InvalidArgumentException("Failed to listen server port [{$config['host']}:{$config['port']}]");
            }
        }
    }

    abstract protected function getServerClass(): string;

    abstract protected function onRequest(): void;
}

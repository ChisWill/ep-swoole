<?php

declare(strict_types=1);

namespace Ep\Swoole;

use InvalidArgumentException;

final class Config
{
    /**
     * Socket controller suffix
     */
    public string $webSocketSuffix = 'Socket';
    /**
     * Main server config
     * 
     * @see https://wiki.swoole.com/#/server/methods?id=__construct
     */
    public string $host = '0.0.0.0';
    public int $port = 9501;
    public int $mode = SWOOLE_PROCESS;
    public int $sockType = SWOOLE_SOCK_TCP;
    /**
     * Main server type
     */
    public int $type = Server::HTTP;
    /**
     * Main server settings
     * 
     * @see https://wiki.swoole.com/#/server/setting
     */
    public array $settings = [];
    /**
     * Main server events
     * 
     * @see https://wiki.swoole.com/#/server/events
     */
    public array $events = [];
    /**
     * Sub server config which is same as main server config
     */
    public array $servers = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }
}

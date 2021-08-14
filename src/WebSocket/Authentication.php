<?php

declare(strict_types=1);

namespace Ep\Swoole\WebSocket;

use Ep\Swoole\Contract\WebSocketIdentityRepositoryInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\QueryParameter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

abstract class Authentication implements AuthenticationMethodInterface
{
    private QueryParameter $queryParameter;

    public function __construct(WebSocketIdentityRepositoryInterface $webSocketIdentityRepository)
    {
        $this->queryParameter = new QueryParameter($webSocketIdentityRepository);
    }

    private int $fd;

    public function withFd(int $fd): self
    {
        $new = clone $this;
        $new->fd = $fd;
        return $new;
    }

    public function withParameterName(string $name): self
    {
        $new = clone $this;
        $new->queryParameter = $new->queryParameter->withParameterName($name);
        return $new;
    }

    public function withTokenType(?string $type): self
    {
        $new = clone $this;
        $new->queryParameter = $new->queryParameter->withTokenType($type);
        return $new;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        if (!isset($this->fd)) {
            throw new LogicException('Must call this method ' . static::class . '::withFd().');
        }

        $identity = $this->queryParameter->authenticate($request);

        if ($identity !== null && ($id = $identity->getId()) !== null) {
            $this->bind($this->fd, $id);
        }

        return $identity;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $this->queryParameter->challenge($response);
    }

    abstract protected function bind(int $fd, string $id): void;
}

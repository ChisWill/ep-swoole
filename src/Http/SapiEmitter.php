<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Yiisoft\Http\Status;
use Swoole\Http\Response;
use Psr\Http\Message\ResponseInterface;

final class SapiEmitter
{
    private const NO_BODY_RESPONSE_CODES = [
        Status::CONTINUE,
        Status::SWITCHING_PROTOCOLS,
        Status::PROCESSING,
        Status::NO_CONTENT,
        Status::RESET_CONTENT,
        Status::NOT_MODIFIED,
    ];

    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function emit(ResponseInterface $response, bool $withoutBody = false): void
    {
        $this->response->setStatusCode($response->getStatusCode());

        $this->emitHeaders($response);

        if (!$withoutBody && $this->shouldOutputBody($response)) {
            $this->emitBody($response);
        }

        $this->response->end();
    }

    private function emitHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $header => $values) {
            $this->response->setHeader($header, $values, false);
        }
    }

    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        $body->rewind();
        while (!$body->eof()) {
            $this->response->write($body->read(2_097_152));
        }
    }

    private function shouldOutputBody(ResponseInterface $response): bool
    {
        return !in_array($response->getStatusCode(), self::NO_BODY_RESPONSE_CODES, true);
    }
}

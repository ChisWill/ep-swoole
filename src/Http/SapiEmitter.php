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
        $withoutBody = $withoutBody || !$this->shouldOutputBody($response);

        $this->response->setStatusCode($response->getStatusCode());

        foreach ($response->getHeaders() as $header => $values) {
            $this->response->setHeader($header, $values, false);
        }

        if (!$withoutBody) {
            $this->emitBody($response);
        }

        $this->response->end();
    }

    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        while (!$body->eof()) {
            $this->response->write($body->read(2_097_152));
        }
    }

    private function shouldOutputBody(ResponseInterface $response): bool
    {
        if (in_array($response->getStatusCode(), self::NO_BODY_RESPONSE_CODES, true)) {
            return false;
        }

        $body = $response->getBody();
        if (!$body->isReadable()) {
            return false;
        }

        $size = $body->getSize();
        if ($size !== null) {
            return $size > 0;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Ep\Web\ServerRequest;
use Yiisoft\Http\Method;
use Swoole\Http\Request;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

final class PsrRequestFactory
{
    private ServerRequestFactoryInterface $serverRequestFactory;
    private UriFactoryInterface $uriFactory;
    private UploadedFileFactoryInterface $uploadedFileFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        UriFactoryInterface $uriFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->uriFactory = $uriFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->streamFactory = $streamFactory;
    }

    public function createFromSwooleRequest(Request $request): ServerRequestInterface
    {
        $method = $request->server['request_method'] ?? Method::GET;
        $uri = $this->getUri($request);
        $serverRequest = new ServerRequest($this->serverRequestFactory->createServerRequest($method, $uri, $request->server));

        foreach ($request->header as $name => $value) {
            if ($name === 'host' && $serverRequest->hasHeader('host')) {
                continue;
            }
            $serverRequest = $serverRequest->withAddedHeader($name, $value);
        }

        return $serverRequest
            ->withProtocolVersion($this->getProtocolVersion($request->server, '1.1'))
            ->withQueryParams($request->get ?: [])
            ->withParsedBody($request->post ?: [])
            ->withCookieParams($request->cookie ?: [])
            ->withUploadedFiles($this->getUploadedFiles($request->files ?: []))
            ->withBody($this->getStream($request));
    }

    private function getProtocolVersion(array $server, string $default): string
    {
        if (array_key_exists('server_protocol', $server) && $server['server_protocol'] !== '') {
            return str_replace('HTTP/', '', $server['server_protocol']);
        } else {
            return $default;
        }
    }

    private function getUri(Request $request): UriInterface
    {
        $uri = $this->uriFactory->createUri();

        if (array_key_exists('https', $request->server) && $request->server['https'] !== '' && $request->server['https'] !== 'off') {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }

        if (isset($request->header['host'])) {
            if (preg_match('/^(.+):(\d+)$/', $request->header['host'], $matches) === 1) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($request->header['host']);
            }
        } elseif (isset($request->server['server_name'])) {
            $uri = $uri->withHost($request->server['server_name']);
        }

        if (isset($request->server['server_port'])) {
            $uri = $uri->withPort($request->server['server_port']);
        }

        if (isset($request->server['request_uri'])) {
            $uri = $uri->withPath(explode('?', $request->server['request_uri'])[0]);
        }

        if (isset($request->server['query_string'])) {
            $uri = $uri->withQuery($request->server['query_string']);
        }

        return $uri;
    }

    private function getStream(Request $request): StreamInterface
    {
        if (strpos($request->header['content-type'] ?? '', 'multipart/form-data') !== false) {
            return $this->streamFactory->createStream();
        } else {
            return $this->streamFactory->createStream($request->getContent());
        }
    }

    private function getUploadedFiles(array $swooleFiles): array
    {
        $files = [];
        if ($swooleFiles) {
            $this->populateUploadedFiles($swooleFiles, $files);
        }
        return $files;
    }

    private function populateUploadedFiles(array $files, array &$result): void
    {
        foreach ($files as $name => $file) {
            $result[$name] = [];
            if (is_array(current($file))) {
                $this->populateUploadedFiles($file, $result[$name]);
            } else {
                try {
                    $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
                } catch (RuntimeException $e) {
                    $stream = $this->streamFactory->createStream();
                }

                $result[$name] = $this->uploadedFileFactory->createUploadedFile(
                    $stream,
                    (int) $file['size'],
                    (int) $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }
    }
}

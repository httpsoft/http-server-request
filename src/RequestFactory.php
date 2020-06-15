<?php

declare(strict_types=1);

namespace HttpSoft\Request;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class RequestFactory implements RequestFactoryInterface
{
    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param StreamInterface|string|resource $body
     * @param array $headers
     * @param string $protocol
     * @return RequestInterface
     */
    public static function create(
        string $method,
        $uri,
        $body = 'php://temp',
        array $headers = [],
        string $protocol = '1.1'
    ): RequestInterface {
        return new Request($method, $uri, $body, $headers, $protocol);
    }

    /**
     * {@inheritDoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}

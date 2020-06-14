<?php

declare(strict_types=1);

namespace HttpSoft\Request;

use Fig\Http\Message\RequestMethodInterface;
use HttpSoft\Uri\UriData;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface, RequestMethodInterface
{
    use RequestTrait;

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param StreamInterface|string|resource $body
     * @param array $headers
     * @param string $protocol
     */
    public function __construct(
        string $method = self::METHOD_GET,
        $uri = UriData::EMPTY_STRING,
        $body = 'php://temp',
        array $headers = [],
        string $protocol = '1.1'
    ) {
        $this->init($method, $uri, $body, $headers, $protocol);
    }
}

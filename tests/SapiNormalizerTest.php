<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ServerRequest;

use HttpSoft\ServerRequest\SapiNormalizer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class SapiNormalizerTest extends TestCase
{
    /**
     * @var SapiNormalizer
     */
    private SapiNormalizer $normalizer;

    /**
     * @var array
     */
    private array $server;

    public function setUp(): void
    {
        $this->normalizer = new SapiNormalizer();
        $this->server = [
            'value-without-name',
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
            'REQUEST_METHOD' => 'GET',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'example.org',
            'HTTP_HOST' => 'example.com',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'CONTENT_TYPE' => 'text/html; charset=UTF-8',
            'REQUEST_URI' => '/path?name=value',
            'QUERY_STRING' => 'name=value',
            'REDIRECT_STATUS' => '200',
            'REDIRECT_HTTP_HOST' => 'example.org',
            'REDIRECT_HTTP_CONNECTION' => 'keep-alive',
        ];
    }

    public function testNormalizeMethod(): void
    {
        $method = $this->normalizer->normalizeMethod($this->server);
        $this->assertSame($this->server['REQUEST_METHOD'], $method);
    }

    public function testNormalizeMethodIfRequestMethodHeaderIsEmptyOrNotExist(): void
    {
        $defaultMethod = 'GET';
        $server = $this->server;

        $server['REQUEST_METHOD'] = null;
        $method = $this->normalizer->normalizeMethod($server);
        $this->assertSame($defaultMethod, $method);

        $server['REQUEST_METHOD'] = '';
        $method = $this->normalizer->normalizeMethod($server);
        $this->assertSame($defaultMethod, $method);

        unset($server['REQUEST_METHOD']);
        $method = $this->normalizer->normalizeMethod($server);
        $this->assertSame($defaultMethod, $method);
    }

    public function testNormalizeProtocolVersion(): void
    {
        $version = $this->normalizer->normalizeProtocolVersion($this->server);
        $this->assertSame($this->server['SERVER_PROTOCOL'], 'HTTP/' . $version);
    }

    public function testNormalizeProtocolVersionIfServerProtocolHeaderIsEmptyOrNotExist(): void
    {
        $defaultVersion = '1.1';
        $server = $this->server;

        $server['SERVER_PROTOCOL'] = null;
        $version = $this->normalizer->normalizeProtocolVersion($server);
        $this->assertSame($defaultVersion, $version);

        $server['SERVER_PROTOCOL'] = '';
        $version = $this->normalizer->normalizeProtocolVersion($server);
        $this->assertSame($defaultVersion, $version);

        unset($server['SERVER_PROTOCOL']);
        $version = $this->normalizer->normalizeProtocolVersion($server);
        $this->assertSame($defaultVersion, $version);
    }

    public function testNormalizeUri(): void
    {
        $uri = $this->normalizer->normalizeUri($this->server);
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame($this->server['HTTP_X_FORWARDED_PROTO'], $uri->getScheme());
        $this->assertSame($this->server['HTTP_HOST'], $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame($this->server['HTTP_HOST'], $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame($this->server['QUERY_STRING'], $uri->getQuery());
        $this->assertSame('https://example.com/path?name=value', (string) $uri);

        unset($this->server['HTTPS'], $this->server['HTTP_HOST']);

        $uri = $this->normalizer->normalizeUri($this->server);
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame($this->server['HTTP_X_FORWARDED_PROTO'], $uri->getScheme());
        $this->assertSame($this->server['SERVER_NAME'], $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame($this->server['SERVER_NAME'], $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame($this->server['QUERY_STRING'], $uri->getQuery());
        $this->assertSame('https://example.org/path?name=value', (string) $uri);
    }

    public function testNormalizeUriIfHttpHostHeaderWithStandardPort(): void
    {
        $uri = $this->normalizer->normalizeUri([
            'SERVER_PORT' => '443',
            'REQUEST_SCHEME' => 'https',
            'HTTP_HOST' => 'example.com:443',
        ]);

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('https://example.com', (string) $uri);
    }

    public function testNormalizeUriIfHttpHostHeaderWithNotStandardPort(): void
    {
        $uri = $this->normalizer->normalizeUri([
            'SERVER_PORT' => '443',
            'REQUEST_SCHEME' => 'https',
            'HTTP_HOST' => 'example.com:8080',
        ]);

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com:8080', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('https://example.com:8080', (string) $uri);
    }

    public function testNormalizeUriIfHttpHostHeaderWithNotEqualStandardPortWithScheme(): void
    {
        $uri = $this->normalizer->normalizeUri([
            'SERVER_PORT' => '443',
            'REQUEST_SCHEME' => 'https',
            'HTTP_HOST' => 'example.com:80',
        ]);

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com:80', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(80, $uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('https://example.com:80', (string) $uri);
    }

    public function testNormalizeUriIfServerIsEmpty(): void
    {
        $uri = $this->normalizer->normalizeUri([]);
        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', (string) $uri);
    }

    public function testNormalizeHeaders(): void
    {
        $headers = $this->normalizer->normalizeHeaders($this->server);

        $this->assertSame($headers, [
            'Host' => 'example.com',
            'Cache-Control' => 'max-age=0',
            'X-Forwarded-Proto' => 'https',
            'Content-Type' => 'text/html; charset=UTF-8',
            'Connection' => 'keep-alive',
        ]);
    }

    public function testNormalizeHeadersIfServerIsEmpty(): void
    {
        $headers = $this->normalizer->normalizeHeaders([]);
        $this->assertSame([], $headers);
    }
}

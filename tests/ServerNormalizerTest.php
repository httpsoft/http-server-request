<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\ServerNormalizer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class ServerNormalizerTest extends TestCase
{
    /**
     * @var ServerNormalizer
     */
    private ServerNormalizer $normalizer;

    /**
     * @var array
     */
    private array $server;

    public function setUp(): void
    {
        $this->normalizer = new ServerNormalizer();
        $this->server = [
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
            'REQUEST_METHOD' => 'GET',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_HOST' => 'example.com',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'CONTENT_TYPE' => 'text/html; charset=UTF-8',
            'REQUEST_URI' => '/path?name=value',
            'QUERY_STRING' => 'name=value',
        ];
    }

    public function testNormalizeMethod(): void
    {
        $method = $this->normalizer->normalizeMethod($this->server);
        self::assertEquals($this->server['REQUEST_METHOD'], $method);
    }

    public function testNormalizeMethodIfRequestMethodHeaderIsEmptyOrNotExist(): void
    {
        $defaultMethod = 'GET';
        $server = $this->server;

        $server['REQUEST_METHOD'] = null;
        $method = $this->normalizer->normalizeMethod($server);
        self::assertEquals($defaultMethod, $method);

        $server['REQUEST_METHOD'] = '';
        $method = $this->normalizer->normalizeMethod($server);
        self::assertEquals($defaultMethod, $method);

        unset($server['REQUEST_METHOD']);
        $method = $this->normalizer->normalizeMethod($server);
        self::assertEquals($defaultMethod, $method);
    }

    public function testNormalizeProtocolVersion(): void
    {
        $version = $this->normalizer->normalizeProtocolVersion($this->server);
        self::assertEquals($this->server['SERVER_PROTOCOL'], 'HTTP/' . $version);
    }

    public function testNormalizeProtocolVersionIfServerProtocolHeaderIsEmptyOrNotExist(): void
    {
        $defaultVersion = '1.1';
        $server = $this->server;

        $server['SERVER_PROTOCOL'] = null;
        $version = $this->normalizer->normalizeProtocolVersion($server);
        self::assertEquals($defaultVersion, $version);

        $server['SERVER_PROTOCOL'] = '';
        $version = $this->normalizer->normalizeProtocolVersion($server);
        self::assertEquals($defaultVersion, $version);

        unset($server['SERVER_PROTOCOL']);
        $version = $this->normalizer->normalizeProtocolVersion($server);
        self::assertEquals($defaultVersion, $version);
    }

    public function testNormalizeUri(): void
    {
        $uri = $this->normalizer->normalizeUri($this->server);
        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertEquals($this->server['HTTP_X_FORWARDED_PROTO'], $uri->getScheme());
        self::assertEquals($this->server['HTTP_HOST'], $uri->getAuthority());
        self::assertEquals('', $uri->getUserInfo());
        self::assertEquals($this->server['HTTP_HOST'], $uri->getHost());
        self::assertEquals(null, $uri->getPort());
        self::assertEquals('/path', $uri->getPath());
        self::assertEquals($this->server['QUERY_STRING'], $uri->getQuery());
        self::assertEquals('https://example.com/path?name=value', (string) $uri);
    }

    public function testNormalizeUriIfServerIsEmpty(): void
    {
        $uri = $this->normalizer->normalizeUri([]);
        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertEquals('', $uri->getScheme());
        self::assertEquals('', $uri->getAuthority());
        self::assertEquals('', $uri->getUserInfo());
        self::assertEquals('', $uri->getHost());
        self::assertEquals(null, $uri->getPort());
        self::assertEquals('', $uri->getPath());
        self::assertEquals('', $uri->getQuery());
        self::assertEquals('', (string) $uri);
    }

    public function testNormalizeHeaders(): void
    {
        $headers = $this->normalizer->normalizeHeaders($this->server);

        self::assertEquals($this->server['HTTP_HOST'], $headers['Host']);
        self::assertEquals($this->server['HTTP_CACHE_CONTROL'], $headers['Cache-Control']);
        self::assertEquals($this->server['HTTP_X_FORWARDED_PROTO'], $headers['X-Forwarded-Proto']);
        self::assertEquals($this->server['CONTENT_TYPE'], $headers['Content-Type']);

        self::assertFalse(isset($headers['HTTPS']));
        self::assertFalse(isset($headers['SERVER_PORT']));
        self::assertFalse(isset($headers['REQUEST_METHOD']));
        self::assertFalse(isset($headers['SERVER_PROTOCOL']));
        self::assertFalse(isset($headers['REQUEST_URI']));
        self::assertFalse(isset($headers['QUERY_STRING']));
    }

    public function testNormalizeHeadersIfServerIsEmpty(): void
    {
        $headers = $this->normalizer->normalizeHeaders([]);
        self::assertEquals([], $headers);
    }
}

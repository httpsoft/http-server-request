<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\SapiNormalizer;
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
        $this->assertSame(null, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame($this->server['QUERY_STRING'], $uri->getQuery());
        $this->assertSame('https://example.com/path?name=value', (string) $uri);
    }

    public function testNormalizeUriIfServerIsEmpty(): void
    {
        $uri = $this->normalizer->normalizeUri([]);
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertSame(null, $uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', (string) $uri);
    }

    public function testNormalizeHeaders(): void
    {
        $headers = $this->normalizer->normalizeHeaders($this->server);

        $this->assertSame($this->server['HTTP_HOST'], $headers['Host']);
        $this->assertSame($this->server['HTTP_CACHE_CONTROL'], $headers['Cache-Control']);
        $this->assertSame($this->server['HTTP_X_FORWARDED_PROTO'], $headers['X-Forwarded-Proto']);
        $this->assertSame($this->server['CONTENT_TYPE'], $headers['Content-Type']);

        $this->assertFalse(isset($headers['HTTPS']));
        $this->assertFalse(isset($headers['SERVER_PORT']));
        $this->assertFalse(isset($headers['REQUEST_METHOD']));
        $this->assertFalse(isset($headers['SERVER_PROTOCOL']));
        $this->assertFalse(isset($headers['REQUEST_URI']));
        $this->assertFalse(isset($headers['QUERY_STRING']));
    }

    public function testNormalizeHeadersIfServerIsEmpty(): void
    {
        $headers = $this->normalizer->normalizeHeaders([]);
        $this->assertSame([], $headers);
    }
}

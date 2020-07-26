<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\Request;
use HttpSoft\Request\RequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class RequestFactoryTest extends TestCase
{
    private string $defaultProtocolVersion = '1.1';

    public function testCreate(): void
    {
        $request = RequestFactory::create($method = 'GET', $uri = 'http://example.com');
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
        $this->assertSame(['Host' => ['example.com']], $request->getHeaders());
        $this->assertSame($this->defaultProtocolVersion, $request->getProtocolVersion());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);

        $request = RequestFactory::create(
            $method = 'POST',
            $uri = 'http://example.com',
            ['Content-Type' => 'text/html'],
            'data://,Content',
            $protocol = '1.0'
        );
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
        $this->assertSame(['Host' => ['example.com'], 'Content-Type' => ['text/html']], $request->getHeaders());
        $this->assertSame($protocol, $request->getProtocolVersion());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('Content', $request->getBody()->getContents());
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testCreateRequest(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest($method = 'GET', $uri = 'http://example.com');
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
        $this->assertSame(['Host' => ['example.com']], $request->getHeaders());
        $this->assertSame($this->defaultProtocolVersion, $request->getProtocolVersion());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
    }
}

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
        self::assertEquals($method, $request->getMethod());
        self::assertEquals($uri, (string) $request->getUri());
        self::assertEquals(['Host' => ['example.com']], $request->getHeaders());
        self::assertEquals($this->defaultProtocolVersion, $request->getProtocolVersion());
        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertInstanceOf(Request::class, $request);

        $request = RequestFactory::create(
            $method = 'POST',
            $uri = 'http://example.com',
            'data://,Content',
            ['Content-Type' => 'text/html'],
            $protocol = '1.0'
        );
        self::assertEquals($method, $request->getMethod());
        self::assertEquals($uri, (string) $request->getUri());
        self::assertEquals(['Host' => ['example.com'], 'Content-Type' => ['text/html']], $request->getHeaders());
        self::assertEquals($protocol, $request->getProtocolVersion());
        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertEquals('Content', $request->getBody()->getContents());
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertInstanceOf(Request::class, $request);
    }

    public function testCreateRequest(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest($method = 'GET', $uri = 'http://example.com');
        self::assertEquals($method, $request->getMethod());
        self::assertEquals($uri, (string) $request->getUri());
        self::assertEquals(['Host' => ['example.com']], $request->getHeaders());
        self::assertEquals($this->defaultProtocolVersion, $request->getProtocolVersion());
        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertInstanceOf(Request::class, $request);
    }
}

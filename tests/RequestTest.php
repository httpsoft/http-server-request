<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use InvalidArgumentException;
use HttpSoft\Request\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use stdClass;

use function array_merge;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    public function setUp(): void
    {
        $this->request = new Request();
    }

    public function testGetDefault(): void
    {
        self::assertEquals('/', $this->request->getRequestTarget());
        self::assertEquals(Request::METHOD_GET, $this->request->getMethod());
        self::assertInstanceOf(UriInterface::class, $this->request->getUri());
    }

    public function testWithRequestTarget(): void
    {
        $request = $this->request->withRequestTarget('*');
        self::assertNotEquals($this->request, $request);
        self::assertEquals('*', $request->getRequestTarget());
    }

    public function testWithRequestTargetHasNotBeenChangedNotClone(): void
    {
        $request = $this->request->withRequestTarget(null);
        self::assertEquals($this->request, $request);
        self::assertEquals('/', $request->getRequestTarget());
    }

    /**
     * @return array
     */
    public function invalidRequestTargetProvider(): array
    {
        return [['/ *'], ['Request Target'], ["Request\nTarget"], ["Request\tTarget"], ["Request\rTarget"]];
    }

    /**
     * @dataProvider invalidRequestTargetProvider
     * @param mixed $requestTarget
     */
    public function testWithRequestTargetThrowExceptionInvalidRequestTarget($requestTarget): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->request->withRequestTarget($requestTarget);
    }

    public function testWithMethod(): void
    {
        $request = $this->request->withMethod(Request::METHOD_POST);
        self::assertNotEquals($this->request, $request);
        self::assertEquals(Request::METHOD_POST, $request->getMethod());

        $request = $this->request->withMethod($method = 'PoSt');
        self::assertNotEquals($this->request, $request);
        self::assertEquals($method, $request->getMethod());
    }

    public function testWithMethodHasNotBeenChangedNotClone(): void
    {
        $request = $this->request->withMethod(Request::METHOD_GET);
        self::assertEquals($this->request, $request);
        self::assertEquals(Request::METHOD_GET, $request->getMethod());
    }

    /**
     * @return array
     */
    public function invalidMethodProvider(): array
    {
        return $this->getInvalidValues([['Met\hod'], ['Met/hod'], ['Met<hod'], ['Met>hod']]);
    }

    /**
     * @dataProvider invalidMethodProvider
     * @param mixed $method
     */
    public function testWithMethodThrowExceptionForInvalidMethod($method): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->request->withMethod($method);
    }

    public function testWithUri(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $request = $this->request->withUri($uri);
        self::assertNotEquals($this->request, $request);
        self::assertEquals($uri, $request->getUri());
    }

    public function testWithUriUpdateHostHeaderFromUri(): void
    {
        $request = new Request('GET', 'http://example.com/path/to/action');
        self::assertEquals(['Host' => ['example.com']], $request->getHeaders());
        self::assertEquals(['example.com'], $request->getHeader('host'));

        $newUri = $request->getUri()->withHost('example.org');

        $newRequest = $request->withUri($newUri);
        self::assertEquals(['Host' => ['example.org']], $newRequest->getHeaders());
        self::assertEquals(['example.org'], $newRequest->getHeader('host'));

        $newRequestWithUriPort = $request->withUri($newUri->withPort(8080));
        self::assertEquals(['Host' => ['example.org:8080']], $newRequestWithUriPort->getHeaders());
        self::assertEquals(['example.org:8080'], $newRequestWithUriPort->getHeader('host'));

        $newRequestWithUriStandardPort = $request->withUri($newUri->withPort(80));
        self::assertEquals(['Host' => ['example.org']], $newRequestWithUriStandardPort->getHeaders());
        self::assertEquals(['example.org'], $newRequestWithUriStandardPort->getHeader('host'));
    }

    /**
     * @return array
     */
    public function invalidUriProvider(): array
    {
        return $this->getInvalidValues();
    }

    /**
     * @dataProvider invalidUriProvider
     * @param mixed $uri
     */
    public function testUriPassingInConstructorThrowExceptionInvalidUri($uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Request(Request::METHOD_GET, $uri);
    }

    /**
     * @param array $values
     * @return array
     */
    private function getInvalidValues(array $values = []): array
    {
        $common = [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'empty-array' => [[]],
            'object' => [new StdClass()],
            'callable' => [fn() => null],
        ];

        if ($values) {
            return array_merge($common, $values);
        }

        return $common;
    }
}

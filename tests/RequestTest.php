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
        $this->assertSame('/', $this->request->getRequestTarget());
        $this->assertSame(Request::METHOD_GET, $this->request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $this->request->getUri());
    }

    public function testWithRequestTarget(): void
    {
        $request = $this->request->withRequestTarget('*');
        $this->assertNotSame($this->request, $request);
        $this->assertSame('*', $request->getRequestTarget());
    }

    public function testWithRequestTargetHasNotBeenChangedNotClone(): void
    {
        $request = $this->request->withRequestTarget(null);
        $this->assertSame($this->request, $request);
        $this->assertSame('/', $request->getRequestTarget());
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
        $this->assertNotSame($this->request, $request);
        $this->assertSame(Request::METHOD_POST, $request->getMethod());

        $request = $this->request->withMethod($method = 'PoSt');
        $this->assertNotSame($this->request, $request);
        $this->assertSame($method, $request->getMethod());
    }

    public function testWithMethodHasNotBeenChangedNotClone(): void
    {
        $request = $this->request->withMethod(Request::METHOD_GET);
        $this->assertSame($this->request, $request);
        $this->assertSame(Request::METHOD_GET, $request->getMethod());
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
        $this->assertNotSame($this->request, $request);
        $this->assertSame($uri, $request->getUri());
    }

    public function testWithUriUpdateHostHeaderFromUri(): void
    {
        $request = new Request('GET', 'http://example.com/path/to/action');
        $this->assertSame(['Host' => ['example.com']], $request->getHeaders());
        $this->assertSame(['example.com'], $request->getHeader('host'));

        $newUri = $request->getUri()->withHost('example.org');

        $newRequest = $request->withUri($newUri);
        $this->assertSame(['Host' => ['example.org']], $newRequest->getHeaders());
        $this->assertSame(['example.org'], $newRequest->getHeader('host'));

        $newRequestWithUriPort = $request->withUri($newUri->withPort(8080));
        $this->assertSame(['Host' => ['example.org:8080']], $newRequestWithUriPort->getHeaders());
        $this->assertSame(['example.org:8080'], $newRequestWithUriPort->getHeader('host'));

        $newRequestWithUriStandardPort = $request->withUri($newUri->withPort(80));
        $this->assertSame(['Host' => ['example.org']], $newRequestWithUriStandardPort->getHeaders());
        $this->assertSame(['example.org'], $newRequestWithUriStandardPort->getHeader('host'));
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

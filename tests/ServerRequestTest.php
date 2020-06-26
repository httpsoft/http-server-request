<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\ServerRequest;
use HttpSoft\UploadedFile\UploadedFile;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use StdClass;

use const UPLOAD_ERR_OK;

class ServerRequestTest extends TestCase
{
    /**
     * @var ServerRequest
     */
    private ServerRequest $request;

    public function setUp(): void
    {
        $this->request = new ServerRequest();
    }

    public function testGetDefault(): void
    {
        self::assertEquals('/', $this->request->getRequestTarget());
        self::assertEquals(ServerRequest::METHOD_GET, $this->request->getMethod());
        self::assertInstanceOf(UriInterface::class, $this->request->getUri());
        self::assertEquals([], $this->request->getAttributes());
        self::assertEquals([], $this->request->getServerParams());
        self::assertEquals([], $this->request->getCookieParams());
        self::assertEquals([], $this->request->getQueryParams());
        self::assertEquals([], $this->request->getUploadedFiles());
        self::assertNull($this->request->getParsedBody());
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
        $request = $this->request->withMethod(ServerRequest::METHOD_POST);
        self::assertNotEquals($this->request, $request);
        self::assertEquals(ServerRequest::METHOD_POST, $request->getMethod());

        $request = $this->request->withMethod($method = 'PoSt');
        self::assertNotEquals($this->request, $request);
        self::assertEquals($method, $request->getMethod());
    }

    public function testWithMethodHasNotBeenChangedNotClone(): void
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_GET);
        self::assertEquals($this->request, $request);
        self::assertEquals(ServerRequest::METHOD_GET, $request->getMethod());
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
        $request = new ServerRequest([], [], [], [], [], 'GET', 'http://example.com/path/to/action');
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
        new ServerRequest([], [], [], [], [], ServerRequest::METHOD_GET, $uri);
    }

    public function testWithAttributeAndGetAttributes(): void
    {
        $request = $this->request->withAttribute('name', 'value');
        self::assertNotEquals($this->request, $request);
        self::assertEquals('value', $request->getAttribute('name'));
        self::assertEquals(['name' => 'value'], $request->getAttributes());
    }

    public function testWithoutAttributeAndGetAttributes(): void
    {
        $firstRequest = $this->request->withAttribute('name', 'value');
        self::assertNotEquals($this->request, $firstRequest);
        self::assertEquals('value', $firstRequest->getAttribute('name'));
        $secondRequest = $firstRequest->withoutAttribute('name');
        self::assertNotEquals($firstRequest, $secondRequest);
        self::assertNull($secondRequest->getAttribute('name'));
        self::assertEquals([], $secondRequest->getAttributes());
    }

    public function testGetAttributePassedDefaultValue(): void
    {
        self::assertNull($this->request->getAttribute('name'));
        self::assertEquals([], $this->request->getAttribute('name', []));
        self::assertEquals(123, $this->request->getAttribute('name', 123));
    }

    public function testWithCookieParams(): void
    {
        $cookieParams = [
            'cookie_name' => 'adf8ck8eb43218g8fa5f8259b6425371',
        ];
        $request = $this->request->withCookieParams($cookieParams);
        self::assertNotEquals($this->request, $request);
        self::assertEquals($cookieParams, $request->getCookieParams());
    }

    public function testWithQueryParams(): void
    {
        $queryParams = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $request = $this->request->withQueryParams($queryParams);
        self::assertNotEquals($this->request, $request);
        self::assertEquals($queryParams, $request->getQueryParams());
    }

    /**
     * @return array
     */
    public function validParsedBodyProvider(): array
    {
        return [
            'object' => [new StdClass()],
            'array' => [['key' => 'value']],
            'empty-array' => [[]],
        ];
    }

    /**
     * @dataProvider validParsedBodyProvider
     * @param mixed $parsedBody
     */
    public function testWithParsedBodyPassedValidParsedBody($parsedBody): void
    {
        $request = $this->request->withParsedBody($parsedBody);
        self::assertNotEquals($this->request, $request);
        self::assertEquals($parsedBody, $request->getParsedBody());
    }

    /**
     * @return array
     */
    public function invalidParsedBodyProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'string' => ['string'],
        ];
    }

    /**
     * @dataProvider invalidParsedBodyProvider
     * @param mixed $parsedBody
     */
    public function testWithParsedBodyThrowExceptionForInvalidParsedBody($parsedBody): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->request->withParsedBody($parsedBody);
    }

    public function testWithUploadedFiles(): void
    {
        $uploadedFiles = [
            new UploadedFile('file.txt', 1024, UPLOAD_ERR_OK),
            new UploadedFile('image.png', 67890, UPLOAD_ERR_OK),
        ];

        $request = $this->request->withUploadedFiles($uploadedFiles);
        self::assertNotEquals($this->request, $request);
        self::assertEquals($uploadedFiles, $request->getUploadedFiles());
    }

    /**
     * @return array
     */
    public function invalidUploadedFilesProvider(): array
    {
        return [
            'array-null' => [[null]],
            'array-true' => [[true]],
            'array-false' => [[false]],
            'array-int' => [[1]],
            'array-float' => [[1.1]],
            'array-string' => [['string']],
            'array-object' => [[new StdClass()]],
            'array-callable' => [[fn() => null]],
        ];
    }

    /**
     * @dataProvider invalidUploadedFilesProvider
     * @param mixed $uploadedFiles
     */
    public function testWithUploadedFilesThrowExceptionForInvalidUploadedFiles($uploadedFiles): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->request->withUploadedFiles($uploadedFiles);
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

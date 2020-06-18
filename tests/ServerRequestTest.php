<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\ServerRequest;
use HttpSoft\UploadedFile\UploadedFile;
use InvalidArgumentException;
use StdClass;

use const UPLOAD_ERR_OK;

class ServerRequestTest extends RequestTest
{
    /**
     * @var ServerRequest
     */
    private ServerRequest $serverRequest;

    public function setUp(): void
    {
        $this->serverRequest = new ServerRequest();
        parent::setUp();
    }

    public function testGetDefault(): void
    {
        self::assertEquals([], $this->serverRequest->getAttributes());
        self::assertEquals([], $this->serverRequest->getServerParams());
        self::assertEquals([], $this->serverRequest->getCookieParams());
        self::assertEquals([], $this->serverRequest->getQueryParams());
        self::assertEquals([], $this->serverRequest->getUploadedFiles());
        self::assertNull($this->serverRequest->getParsedBody());
    }

    public function testWithAttributeAndGetAttributes(): void
    {
        $request = $this->serverRequest->withAttribute('name', 'value');
        self::assertNotEquals($this->serverRequest, $request);
        self::assertEquals('value', $request->getAttribute('name'));
        self::assertEquals(['name' => 'value'], $request->getAttributes());
    }

    public function testWithoutAttributeAndGetAttributes(): void
    {
        $firstRequest = $this->serverRequest->withAttribute('name', 'value');
        self::assertNotEquals($this->serverRequest, $firstRequest);
        self::assertEquals('value', $firstRequest->getAttribute('name'));
        $secondRequest = $firstRequest->withoutAttribute('name');
        self::assertNotEquals($firstRequest, $secondRequest);
        self::assertNull($secondRequest->getAttribute('name'));
        self::assertEquals([], $secondRequest->getAttributes());
    }

    public function testGetAttributePassedDefaultValue(): void
    {
        self::assertNull($this->serverRequest->getAttribute('name'));
        self::assertEquals([], $this->serverRequest->getAttribute('name', []));
        self::assertEquals(123, $this->serverRequest->getAttribute('name', 123));
    }

    public function testWithCookieParams(): void
    {
        $cookieParams = [
            'cookie_name' => 'adf8ck8eb43218g8fa5f8259b6425371',
        ];
        $request = $this->serverRequest->withCookieParams($cookieParams);
        self::assertNotEquals($this->serverRequest, $request);
        self::assertEquals($cookieParams, $request->getCookieParams());
    }

    public function testWithQueryParams(): void
    {
        $queryParams = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $request = $this->serverRequest->withQueryParams($queryParams);
        self::assertNotEquals($this->serverRequest, $request);
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
        $request = $this->serverRequest->withParsedBody($parsedBody);
        self::assertNotEquals($this->serverRequest, $request);
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
        $this->serverRequest->withParsedBody($parsedBody);
    }

    public function testWithUploadedFiles(): void
    {
        $uploadedFiles = [
            new UploadedFile('file.txt', 1024, UPLOAD_ERR_OK),
            new UploadedFile('image.png', 67890, UPLOAD_ERR_OK),
        ];

        $request = $this->serverRequest->withUploadedFiles($uploadedFiles);
        self::assertNotEquals($this->serverRequest, $request);
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
        $this->serverRequest->withUploadedFiles($uploadedFiles);
    }
}

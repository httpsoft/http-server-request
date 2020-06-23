<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Request;

use HttpSoft\Request\SapiNormalizer;
use HttpSoft\Request\ServerRequest;
use HttpSoft\Request\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use const UPLOAD_ERR_OK;

class ServerRequestFactoryTest extends TestCase
{
    /**
     * @var SapiNormalizer
     */
    private SapiNormalizer $serverNormalizer;

    public function setUp(): void
    {
        $this->serverNormalizer = new SapiNormalizer();
    }

    public function testCreate(): void
    {
        $serverRequest = ServerRequestFactory::create();
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertNotEmpty($serverRequest->getServerParams());
        self::assertEquals([], $serverRequest->getUploadedFiles());
        self::assertEquals([], $serverRequest->getCookieParams());
        self::assertEquals([], $serverRequest->getQueryParams());
        self::assertEquals([], $serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://input', $serverRequest->getBody()->getMetadata('uri'));

        $serverRequest = ServerRequestFactory::create($this->serverNormalizer);
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertNotEmpty($serverRequest->getServerParams());
        self::assertEquals([], $serverRequest->getUploadedFiles());
        self::assertEquals([], $serverRequest->getCookieParams());
        self::assertEquals([], $serverRequest->getQueryParams());
        self::assertEquals([], $serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithDefaultValues(): void
    {
        $serverRequest = ServerRequestFactory::createFromGlobals();
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertNotEmpty($serverRequest->getServerParams());
        self::assertEquals([], $serverRequest->getUploadedFiles());
        self::assertEquals([], $serverRequest->getCookieParams());
        self::assertEquals([], $serverRequest->getQueryParams());
        self::assertEquals([], $serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithProvidedEmptyArrays(): void
    {
        $serverRequest = ServerRequestFactory::createFromGlobals(
            $server = [],
            $files = [],
            $cookie = [],
            $get = [],
            $post = [],
            $this->serverNormalizer
        );
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertEquals($server, $serverRequest->getServerParams());
        self::assertEquals($files, $serverRequest->getUploadedFiles());
        self::assertEquals($cookie, $serverRequest->getCookieParams());
        self::assertEquals($get, $serverRequest->getQueryParams());
        self::assertEquals($post, $serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithProvidedNotEmptyArrays(): void
    {
        $serverRequest = ServerRequestFactory::createFromGlobals(
            $server = [
                'HTTP_HOST' => 'example.com',
                'HTTP_COOKIE' => 'cookie-key=cookie-value',
                'CONTENT_TYPE' => 'text/html; charset=UTF-8',
                'REQUEST_URI' => '/path?get-key=get-value',
                'QUERY_STRING' => 'get-key=get-value',
            ],
            $files = [
                'file' => [
                    'name' => 'file.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpN3FmFr',
                    'error' => UPLOAD_ERR_OK,
                    'size' => 1024,
                ],
            ],
            $cookie = ['cookie-key' => 'cookie-value'],
            $get = ['get-key' => 'get-value'],
            $post = ['post-key' => 'post-value'],
            $this->serverNormalizer
        );
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertEquals($server, $serverRequest->getServerParams());
        self::assertEquals($files['file']['name'], $serverRequest->getUploadedFiles()['file']->getClientFilename());
        self::assertEquals($cookie, $serverRequest->getCookieParams());
        self::assertEquals($get, $serverRequest->getQueryParams());
        self::assertEquals($post, $serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://input', $serverRequest->getBody()->getMetadata('uri'));
        self::assertEquals(
            [
                'Host' => ['example.com'],
                'Cookie' => ['cookie-key=cookie-value'],
                'Content-Type' => ['text/html; charset=UTF-8'],
            ],
            $serverRequest->getHeaders()
        );
    }

    public function testCreateServerRequest(): void
    {
        $factory = new ServerRequestFactory();

        $serverRequest = $factory->createServerRequest('GET', 'https://example.com');
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertEquals([], $serverRequest->getServerParams());
        self::assertEquals([], $serverRequest->getUploadedFiles());
        self::assertEquals([], $serverRequest->getCookieParams());
        self::assertEquals([], $serverRequest->getQueryParams());
        self::assertNull($serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://temp', $serverRequest->getBody()->getMetadata('uri'));

        $serverRequest = $factory->createServerRequest('GET', 'https://example.com', $server = [
            'HTTP_HOST' => 'example.com',
            'CONTENT_TYPE' => 'text/html; charset=UTF-8',
        ]);
        self::assertInstanceOf(ServerRequest::class, $serverRequest);
        self::assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        self::assertEquals($server, $serverRequest->getServerParams());
        self::assertEquals([], $serverRequest->getUploadedFiles());
        self::assertEquals([], $serverRequest->getCookieParams());
        self::assertEquals([], $serverRequest->getQueryParams());
        self::assertNull($serverRequest->getParsedBody());
        self::assertEquals([], $serverRequest->getAttributes());
        self::assertEquals('php://temp', $serverRequest->getBody()->getMetadata('uri'));
    }
}

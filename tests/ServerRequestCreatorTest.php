<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ServerRequest;

use HttpSoft\Message\ServerRequest;
use HttpSoft\ServerRequest\SapiNormalizer;
use HttpSoft\ServerRequest\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use const UPLOAD_ERR_OK;

class ServerRequestCreatorTest extends TestCase
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
        $serverRequest = ServerRequestCreator::create();
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertNotEmpty($serverRequest->getServerParams());
        $this->assertSame([], $serverRequest->getUploadedFiles());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame([], $serverRequest->getQueryParams());
        $this->assertSame([], $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getAttributes());
        $this->assertSame('php://input', $serverRequest->getBody()->getMetadata('uri'));

        $serverRequest = ServerRequestCreator::create($this->serverNormalizer);
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertNotEmpty($serverRequest->getServerParams());
        $this->assertSame([], $serverRequest->getUploadedFiles());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame([], $serverRequest->getQueryParams());
        $this->assertSame([], $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getAttributes());
        $this->assertSame('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithDefaultValues(): void
    {
        $serverRequest = ServerRequestCreator::createFromGlobals();
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertNotEmpty($serverRequest->getServerParams());
        $this->assertSame([], $serverRequest->getUploadedFiles());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame([], $serverRequest->getQueryParams());
        $this->assertSame([], $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getAttributes());
        $this->assertSame('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithProvidedEmptyArrays(): void
    {
        $serverRequest = ServerRequestCreator::createFromGlobals(
            $server = [],
            $files = [],
            $cookie = [],
            $get = [],
            $post = [],
            $this->serverNormalizer
        );
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertSame($server, $serverRequest->getServerParams());
        $this->assertSame($files, $serverRequest->getUploadedFiles());
        $this->assertSame($cookie, $serverRequest->getCookieParams());
        $this->assertSame($get, $serverRequest->getQueryParams());
        $this->assertSame($post, $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getAttributes());
        $this->assertSame('php://input', $serverRequest->getBody()->getMetadata('uri'));
    }

    public function testCreateFromGlobalsWithProvidedNotEmptyArrays(): void
    {
        $serverRequest = ServerRequestCreator::createFromGlobals(
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
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertSame($server, $serverRequest->getServerParams());
        $this->assertSame($files['file']['name'], $serverRequest->getUploadedFiles()['file']->getClientFilename());
        $this->assertSame($cookie, $serverRequest->getCookieParams());
        $this->assertSame($get, $serverRequest->getQueryParams());
        $this->assertSame($post, $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getAttributes());
        $this->assertSame('php://input', $serverRequest->getBody()->getMetadata('uri'));
        $this->assertSame(
            [
                'Host' => ['example.com'],
                'Cookie' => ['cookie-key=cookie-value'],
                'Content-Type' => ['text/html; charset=UTF-8'],
            ],
            $serverRequest->getHeaders()
        );
    }
}

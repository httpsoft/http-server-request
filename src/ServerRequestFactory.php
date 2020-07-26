<?php

declare(strict_types=1);

namespace HttpSoft\Request;

use HttpSoft\Stream\StreamPhpInput;
use HttpSoft\UploadedFile\UploadedFileNormalizer;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param ServerNormalizerInterface|null $normalizer
     * @return ServerRequestInterface
     */
    public static function create(ServerNormalizerInterface $normalizer = null): ServerRequestInterface
    {
        return self::createFromGlobals(null, null, null, null, null, $normalizer);
    }

    /**
     * @param array|null $server
     * @param array|null $files
     * @param array|null $cookie
     * @param array|null $get
     * @param array|null $post
     * @param ServerNormalizerInterface|null $normalizer
     * @return ServerRequestInterface
     */
    public static function createFromGlobals(
        array $server = null,
        array $files = null,
        array $cookie = null,
        array $get = null,
        array $post = null,
        ServerNormalizerInterface $normalizer = null
    ): ServerRequestInterface {
        $server ??= $_SERVER;
        $normalizer ??= new SapiNormalizer();

        return new ServerRequest(
            $server,
            UploadedFileNormalizer::normalize($files ?? $_FILES),
            $cookie ?? $_COOKIE,
            $get ?? $_GET,
            $post ?? $_POST,
            $normalizer->normalizeMethod($server),
            $normalizer->normalizeUri($server),
            $normalizer->normalizeHeaders($server),
            new StreamPhpInput(),
            $normalizer->normalizeProtocolVersion($server)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($serverParams, [], [], [], null, $method, $uri, [], 'php://temp');
    }
}

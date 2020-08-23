<?php

declare(strict_types=1);

namespace HttpSoft\ServerRequest;

use HttpSoft\Message\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestCreator
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
            UploadedFileCreator::createFromGlobals($files ?? $_FILES),
            $cookie ?? $_COOKIE,
            $get ?? $_GET,
            $post ?? $_POST,
            $normalizer->normalizeMethod($server),
            $normalizer->normalizeUri($server),
            $normalizer->normalizeHeaders($server),
            new PhpInputStream(),
            $normalizer->normalizeProtocolVersion($server)
        );
    }
}

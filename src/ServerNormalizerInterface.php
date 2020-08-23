<?php

declare(strict_types=1);

namespace HttpSoft\ServerRequest;

use Psr\Http\Message\UriInterface;

interface ServerNormalizerInterface
{
    /**
     * Returns a normalized request method from server parameters.
     *
     * @param array $server if PHP SAPI is used, it is $_SERVER.
     * @return string request method.
     */
    public function normalizeMethod(array $server): string;

    /**
     * Returns a normalized protocol version from server parameters.
     *
     * @param array $server if PHP SAPI is used, it is $_SERVER.
     * @return string protocol version.
     */
    public function normalizeProtocolVersion(array $server): string;

    /**
     * Returns a normalized request URI from server parameters.
     *
     * @param array $server if PHP SAPI is used, it is $_SERVER.
     * @return UriInterface instance.
     */
    public function normalizeUri(array $server): UriInterface;

    /**
     * Returns a primary normalized headers from server parameters.
     *
     * @param array $server if PHP SAPI is used, it is $_SERVER.
     * @return array `name => value` header pairs.
     */
    public function normalizeHeaders(array $server): array;
}

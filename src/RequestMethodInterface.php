<?php

declare(strict_types=1);

namespace HttpSoft\Request;

interface RequestMethodInterface
{
    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.1
     */
    public const METHOD_GET = 'GET';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.2
     */
    public const METHOD_HEAD = 'HEAD';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.3
     */
    public const METHOD_POST = 'POST';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.4
     */
    public const METHOD_PUT = 'PUT';

    /**
     * @link https://tools.ietf.org/html/rfc5789#section-2
     */
    public const METHOD_PATCH = 'PATCH';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.5
     */
    public const METHOD_DELETE = 'DELETE';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.6
     */
    public const METHOD_CONNECT = 'CONNECT';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.7
     */
    public const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.8
     */
    public const METHOD_TRACE = 'TRACE';

    /**
     * Array/List of all defined methods.
     */
    public const METHODS = [
        self::METHOD_GET,
        self::METHOD_HEAD,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
        self::METHOD_DELETE,
        self::METHOD_CONNECT,
        self::METHOD_OPTIONS,
        self::METHOD_TRACE,
    ];
}

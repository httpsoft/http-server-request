<?php

declare(strict_types=1);

namespace HttpSoft\ServerRequest;

use HttpSoft\Message\Uri;
use Psr\Http\Message\UriInterface;

use function array_key_exists;
use function explode;
use function in_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function strpos;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

final class SapiNormalizer implements ServerNormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalizeMethod(array $server): string
    {
        if (empty($server['REQUEST_METHOD'])) {
            return 'GET';
        }

        return (string) $server['REQUEST_METHOD'];
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeProtocolVersion(array $server): string
    {
        if (empty($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        return str_replace('HTTP/', '', (string) $server['SERVER_PROTOCOL']);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedAssignment
     */
    public function normalizeUri(array $server): UriInterface
    {
        $uri = new Uri();

        if (isset($server['HTTPS']) && in_array(strtolower((string) $server['HTTPS']), ['on', '1'])) {
            $uri = $uri->withScheme('https');
        } elseif ($scheme = $server['HTTP_X_FORWARDED_PROTO'] ?? $server['REQUEST_SCHEME'] ?? '') {
            $uri = $uri->withScheme((string) $scheme);
        }

        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort((int) $server['SERVER_PORT']);
        }

        if ($host = $server['HTTP_X_FORWARDED_HOST'] ?? $server['HTTP_HOST'] ?? '') {
            $uri = preg_match('/^(.+):(\d+)$/', (string) $host, $matches) === 1
                ? $uri->withHost($matches[1])->withPort((int) $matches[2])
                : $uri->withHost((string) $host);
        } elseif ($host = $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '') {
            $uri = $uri->withHost((string) $host);
        }

        if ($path = $server['REQUEST_URI'] ?? $server['ORIG_PATH_INFO'] ?? '') {
            $uri = $uri->withPath(explode('?', preg_replace('/^[^\/:]+:\/\/[^\/]+/', '', (string) $path), 2)[0]);
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery((string) $server['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedAssignment
     */
    public function normalizeHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $name => $value) {
            if (!is_string($name)) {
                continue;
            }

            if (strpos($name, 'REDIRECT_') === 0) {
                if (array_key_exists($name = substr($name, 9), $server)) {
                    continue;
                }
            }

            if (strpos($name, 'HTTP_') === 0) {
                $headers[$this->normalizeHeaderName(substr($name, 5))] = $value;
                continue;
            }

            if (strpos($name, 'CONTENT_') === 0) {
                $headers[$this->normalizeHeaderName($name)] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeHeaderName(string $name): string
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
    }
}

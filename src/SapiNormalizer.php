<?php

declare(strict_types=1);

namespace HttpSoft\Request;

use HttpSoft\Uri\Uri;
use HttpSoft\Uri\UriData;
use Psr\Http\Message\UriInterface;

use function array_key_exists;
use function explode;
use function in_array;
use function is_string;
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
            return RequestMethodInterface::METHOD_GET;
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
     */
    public function normalizeUri(array $server): UriInterface
    {
        $uriData = new UriData();

        if (isset($server['HTTPS']) && in_array(strtolower((string) $server['HTTPS']), ['on', '1'])) {
            $uriData->setScheme(UriData::SCHEMES[UriData::SECURE_PORT]);
        } elseif ($scheme = $server['HTTP_X_FORWARDED_PROTO'] ?? $server['REQUEST_SCHEME'] ?? UriData::EMPTY_STRING) {
            $uriData->setScheme((string) $scheme);
        }

        if ($host = $server['HTTP_X_FORWARDED_HOST'] ?? $server['HTTP_HOST'] ?? UriData::EMPTY_STRING) {
            $uriData->setHost((string) $host);
        } elseif ($host = $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? UriData::EMPTY_STRING) {
            $uriData->setHost((string) $host);
        }

        if (($port = $server['SERVER_PORT'] ?? null) && (strpos($uriData->getHost(), ':') === false)) {
            $uriData->setPort((int) $port);
        }

        if ($path = $server['REQUEST_URI'] ?? $server['ORIG_PATH_INFO'] ?? UriData::EMPTY_STRING) {
            $uriData->setPath(explode('?', preg_replace('/^[^\/:]+:\/\/[^\/]+/', '', (string) $path), 2)[0]);
        }

        if ($query = $server['QUERY_STRING'] ?? UriData::EMPTY_STRING) {
            $uriData->setQuery((string) $query);
        }

        return new Uri($uriData->__toString());
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $name => $value) {
            if (!is_string($name) ||  $value === '') {
                continue;
            }

            if (strpos($name, 'REDIRECT_') === 0) {
                if (array_key_exists($name = substr($name, 9), $server)) {
                    continue;
                }
            }

            if (strpos($name, 'HTTP_') === 0) {
                $headers[$this->normalizeHeaderName(substr($name, 5))] = $value;
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

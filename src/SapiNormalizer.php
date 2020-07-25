<?php

declare(strict_types=1);

namespace HttpSoft\Request;

use HttpSoft\Uri\UriFactory;
use Psr\Http\Message\UriInterface;

use function array_key_exists;
use function is_string;
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
        return UriFactory::createFromServer($server);
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

# HTTP Request

[![License](https://poser.pugx.org/httpsoft/http-request/license)](https://packagist.org/packages/httpsoft/http-request)
[![Latest Stable Version](https://poser.pugx.org/httpsoft/http-request/v)](https://packagist.org/packages/httpsoft/http-request)
[![Total Downloads](https://poser.pugx.org/httpsoft/http-request/downloads)](https://packagist.org/packages/httpsoft/http-request)
[![GitHub Build Status](https://github.com/httpsoft/http-request/workflows/build/badge.svg)](https://github.com/httpsoft/http-request/actions)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/httpsoft/http-request/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-request/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/httpsoft/http-request/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-request/?branch=master)

This package implements [Psr\Http\Message\RequestInterface](https://github.com/php-fig/http-message/blob/master/src/RequestInterface.php) and [Psr\Http\Message\ServerRequestInterface](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) from [PSR-7 HTTP Message](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md) in accordance with the [RFC 7230](https://tools.ietf.org/html/rfc7230) and [RFC 7231](https://tools.ietf.org/html/rfc7231) specifications.

## Documentation

* [In English language](https://httpsoft.org/docs/request).
* [In Russian language](https://httpsoft.org/ru/docs/request).

## Installation

This package requires PHP version 7.4 or later.

```
composer require httpsoft/http-request
```

## Usage Request

```php
use HttpSoft\Request\Request;
use HttpSoft\Request\RequestFactory;

$request = new Request('POST', 'http://example.com', ['Content-Type' => 'text/html'], 'data://,Content', '2');
// Or using the factory:
$request = RequestFactory::create(
    'POST', // Request method
    'https://example.com', // Request URI
    ['Content-Type' => 'text/html' /* Other headers */],
    'data://,Content', // HTTP message body
    '2' // HTTP protocol version
);

$request->getMethod(); // 'POST'
$request->getProtocolVersion(); // '2'
$request->getBody()->getContents(); // 'Content'
(string) $request->getUri(); // 'https://example.com/path'
$request->getHeaders(); // ['Host' => ['example.com'], 'Content-Type' => ['text/html']]
```

## Usage ServerRequest

```php
use HttpSoft\Request\ServerRequest;
use HttpSoft\Request\ServerRequestFactory;

$request = new ServerRequest(
    $_SERVER,
    $_FILES,
    $_COOKIE,
    $_GET,
    $_POST,
    'GET', // Request method
    'https://example.com', // Request URI
    [/* Headers */],
    'php://input', // HTTP message body
    '2' // HTTP protocol version
);
// Or using the factory (all necessary data will be received automatically):
$request = ServerRequestFactory::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST);
// equivalently to:
$request = ServerRequestFactory::createFromGlobals();
// equivalently to:
$request = ServerRequestFactory::create();

$request->getMethod(); // 'GET'
$request->getProtocolVersion(); // '2'
$request->getBody()->getContents(); // ''
(string) $request->getUri(); // 'https://example.com'
$request->getHeaders(); // ['Host' => ['example.com']]
$request->getServerParams(); // $_SERVER
$request->getUploadedFiles(); // $_FILES
$request->getCookieParams(); // $_COOKIE
$request->getQueryParams(); // $_GET
$request->getParsedBody(); // $_POST
$request->getAttributes(); // []
```

By default [HttpSoft\Request\SapiNormalizer](https://github.com/httpsoft/http-request/blob/master/src/SapiNormalizer.php) is used for normalization of server parameters. You can use your own server parameters normalizer, for this you need to implement an [HttpSoft\Request\ServerNormalizerInterface](https://github.com/httpsoft/http-request/blob/master/src/ServerNormalizerInterface.php) interface.

```php
$normalizer = new YouCustomServerNormalizer();

$request = ServerRequestFactory::create($normalizer);
// equivalently to:
$request = ServerRequestFactory::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST, $normalizer);
// or with custom superglobals
$request = ServerRequestFactory::createFromGlobals($server, $files, $cookie, $get, $post, $normalizer);
```

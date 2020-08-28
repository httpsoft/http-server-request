# HTTP Server Request

[![License](https://poser.pugx.org/httpsoft/http-server-request/license)](https://packagist.org/packages/httpsoft/http-server-request)
[![Latest Stable Version](https://poser.pugx.org/httpsoft/http-server-request/v)](https://packagist.org/packages/httpsoft/http-server-request)
[![Total Downloads](https://poser.pugx.org/httpsoft/http-server-request/downloads)](https://packagist.org/packages/httpsoft/http-server-request)
[![GitHub Build Status](https://github.com/httpsoft/http-server-request/workflows/build/badge.svg)](https://github.com/httpsoft/http-server-request/actions)
[![GitHub Static Analysis Status](https://github.com/httpsoft/http-server-request/workflows/static/badge.svg)](https://github.com/httpsoft/http-server-request/actions)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/httpsoft/http-server-request/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-server-request/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/httpsoft/http-server-request/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-server-request/?branch=master)

This package makes it easy and flexible to create PSR-7 components [ServerRequest](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) and [UploadedFile](https://github.com/php-fig/http-message/blob/master/src/UploadedFileInterface.php).

Depends on the [httpsoft/http-message](https://github.com/httpsoft/http-message) package.

## Documentation

* [In English language](https://httpsoft.org/docs/server-request).
* [In Russian language](https://httpsoft.org/ru/docs/server-request).

## Installation

This package requires PHP version 7.4 or later.

```
composer require httpsoft/http-server-request
```

## Usage ServerRequestCreator

```php
use HttpSoft\ServerRequest\ServerRequestCreator;

// All necessary data will be received automatically:
$request = ServerRequestCreator::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST);
// equivalently to:
$request = ServerRequestCreator::createFromGlobals();
// equivalently to:
$request = ServerRequestCreator::create();
```

By default [HttpSoft\ServerRequest\SapiNormalizer](https://github.com/httpsoft/http-server-request/blob/master/src/SapiNormalizer.php) is used for normalization of server parameters. You can use your own server parameters normalizer, for this you need to implement an [HttpSoft\ServerRequest\ServerNormalizerInterface](https://github.com/httpsoft/http-server-request/blob/master/src/ServerNormalizerInterface.php) interface.

```php
$normalizer = new YouCustomServerNormalizer();

$request = ServerRequestCreator::create($normalizer);
// equivalently to:
$request = ServerRequestCreator::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST, $normalizer);
// or with custom superglobals:
$request = ServerRequestCreator::createFromGlobals($server, $files, $cookie, $get, $post, $normalizer);
```

## Usage UploadedFileCreator

```php
use HttpSoft\ServerRequest\UploadedFileCreator;

/** @var StreamInterface|string|resource $streamOrFile */
$uploadedFile = UploadedFileCreator::create($streamOrFile, 1024, UPLOAD_ERR_OK, 'file.txt', 'text/plain');

// Create a new `HttpSoft\UploadedFile\UploadedFile` instance from array (the item `$_FILES`)
$uploadedFile = UploadedFileCreator::createFromArray([
    'name' => 'filename.jpg', // optional
    'type' => 'image/jpeg', // optional
    'tmp_name' => '/tmp/php/php6hst32',
    'error' => 0, // UPLOAD_ERR_OK
    'size' => 98174,
]);

// Normalizes the superglobal structure and converts each array
// value to an instance of `Psr\Http\Message\UploadedFileInterface`.
$uploadedFiles = UploadedFileCreator::createFromGlobals($_FILES);
```

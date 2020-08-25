<?php

declare(strict_types=1);

namespace HttpSoft\ServerRequest;

use HttpSoft\Message\UploadedFile;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

use function is_array;
use function sprintf;

final class UploadedFileCreator
{
    /**
     * Creates an instance of `Psr\Http\Message\UploadedFileInterface`.
     *
     * @param StreamInterface|string|resource $streamOrFile
     * @param int $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFileInterface
     */
    public static function create(
        $streamOrFile,
        int $size,
        int $error,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        return new UploadedFile($streamOrFile, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * Creates an instance of `Psr\Http\Message\UploadedFileInterface` from an one-dimensional array `$file`.
     * The array structure must be the same as item in the global `$_FILES` array.
     *
     * Example of array structure format:
     *
     * ```php
     * $file = [
     *     'name' => 'filename.jpg', // optional
     *     'type' => 'image/jpeg', // optional
     *     'tmp_name' => '/tmp/php/php6hst32',
     *     'error' => 0, // UPLOAD_ERR_OK
     *     'size' => 98174,
     * ];
     * ```
     *
     * @see https://www.php.net/manual/features.file-upload.post-method.php
     * @see https://www.php.net/manual/reserved.variables.files.php
     *
     * @param array $file
     * @return UploadedFileInterface
     * @throws InvalidArgumentException
     * @psalm-suppress MixedArgument
     */
    public static function createFromArray(array $file): UploadedFileInterface
    {
        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid array `$file` to `%s`. One of the items is missing: "tmp_name" or "size" or "error".',
                __METHOD__
            ));
        }

        return new UploadedFile(
            $file['tmp_name'],
            $file['size'],
            $file['error'],
            $file['name'] ?? null,
            $file['type'] ?? null
        );
    }

    /**
     * Converts each value of the multidimensional array `$files`
     * to an `Psr\Http\Message\UploadedFileInterface` instance.
     *
     * The method uses recursion, so the `$files` array can be of any nesting type.
     * The array structure must be the same as the global `$_FILES` array.
     * All key names in the `$files` array will be saved.
     *
     * @see https://www.php.net/manual/features.file-upload.post-method.php
     * @see https://www.php.net/manual/reserved.variables.files.php
     *
     * @param array $files
     * @return UploadedFileInterface[]|array[]
     * @throws InvalidArgumentException
     * @psalm-suppress MixedAssignment
     */
    public static function createFromGlobals(array $files = []): array
    {
        $uploadedFiles = [];

        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFileInterface) {
                $uploadedFiles[$key] = $file;
                continue;
            }

            if (!is_array($file)) {
                throw new InvalidArgumentException(sprintf(
                    'Error in the `%s`. Invalid file specification for normalize in array `$files`.',
                    __METHOD__
                ));
            }

            if (!isset($file['tmp_name'])) {
                $uploadedFiles[$key] = self::createFromGlobals($file);
                continue;
            }

            if (is_array($file['tmp_name'])) {
                $uploadedFiles[$key] = self::createMultipleUploadedFiles($file);
                continue;
            }

            $uploadedFiles[$key] = self::createFromArray($file);
        }

        return $uploadedFiles;
    }

    /**
     * Creates an array instances of `Psr\Http\Message\UploadedFileInterface` from an multidimensional array `$files`.
     * The array structure must be the same as multidimensional item in the global `$_FILES` array.
     *
     * @param array $files
     * @return UploadedFileInterface[]
     * @throws InvalidArgumentException
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private static function createMultipleUploadedFiles(array $files): array
    {
        if (
            !isset($files['tmp_name']) || !is_array($files['tmp_name'])
            || !isset($files['size']) || !is_array($files['size'])
            || !isset($files['error']) || !is_array($files['error'])
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid array `$files` to `%s`. One of the items is missing or is not an array:'
                . ' "tmp_name" or "size" or "error".',
                __METHOD__
            ));
        }

        return self::buildTree(
            $files['tmp_name'],
            $files['size'],
            $files['error'],
            $files['name'] ?? null,
            $files['type'] ?? null,
        );
    }

    /**
     * Building a normalized tree with the correct nested structure
     * and `Psr\Http\Message\UploadedFileInterface` instances.
     *
     * @param string[]|array[] $tmpNames
     * @param int[]|array[] $sizes
     * @param int[]|array[] $errors
     * @param string[]|array[]|null $names
     * @param string[]|array[]|null $types
     * @return UploadedFileInterface[]
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private static function buildTree(array $tmpNames, array $sizes, array $errors, ?array $names, ?array $types): array
    {
        $tree = [];

        foreach ($tmpNames as $key => $value) {
            if (is_array($value)) {
                $tree[$key] = self::buildTree(
                    $tmpNames[$key],
                    $sizes[$key],
                    $errors[$key],
                    $names[$key] ?? null,
                    $types[$key] ?? null,
                );
            } else {
                $tree[$key] = self::createFromArray([
                    'tmp_name' => $tmpNames[$key],
                    'size' => $sizes[$key],
                    'error' => $errors[$key],
                    'name' => $names[$key] ?? null,
                    'type' => $types[$key] ?? null,
                ]);
            }
        }

        return $tree;
    }
}

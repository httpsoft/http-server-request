<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ServerRequest;

use HttpSoft\Message\Stream;
use HttpSoft\Message\UploadedFile;
use HttpSoft\ServerRequest\UploadedFileCreator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;

use function file_exists;
use function sys_get_temp_dir;
use function tempnam;

use const UPLOAD_ERR_OK;

class UploadedFileCreatorTest extends TestCase
{
    /**
     * @var string
     */
    private string $tmpFile = '';

    public function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'httpsoft');
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testCreate(): void
    {
        $uploadedFile = UploadedFileCreator::create($this->tmpFile, $size = 1024, UPLOAD_ERR_OK);
        $this->assertInstanceOf(StreamInterface::class, $uploadedFile->getStream());
        $this->assertInstanceOf(Stream::class, $uploadedFile->getStream());
        $this->assertSame($size, $uploadedFile->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());

        $uploadedFile = UploadedFileCreator::create(
            $stream = new Stream($this->tmpFile),
            $size = 1024,
            UPLOAD_ERR_OK,
            $clientFilename = 'file.txt',
            $clientMediaType = 'text/plain'
        );
        $this->assertInstanceOf(StreamInterface::class, $uploadedFile->getStream());
        $this->assertInstanceOf(Stream::class, $uploadedFile->getStream());
        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertSame($size, $uploadedFile->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame($clientFilename, $uploadedFile->getClientFilename());
        $this->assertSame($clientMediaType, $uploadedFile->getClientMediaType());
    }

    public function testCreateFromArray(): void
    {
        $file = [
            'name' => 'file.txt',
            'type' => 'text/plain',
            'tmp_name' => $this->tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];
        $uploadedFile = UploadedFileCreator::createFromArray($file);
        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
        $this->assertSame($file['name'], $uploadedFile->getClientFilename());
        $this->assertSame($file['type'], $uploadedFile->getClientMediaType());
        $this->assertSame($file['tmp_name'], $uploadedFile->getStream()->getMetadata('uri'));
        $this->assertSame($file['error'], $uploadedFile->getError());
        $this->assertSame($file['size'], $uploadedFile->getSize());
    }

    public function testCreateFromArrayThrowExceptionForInvalidArrayStructure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UploadedFileCreator::createFromArray([
            'file1' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
                'tmp_name' => $this->tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024,
            ],
        ]);
    }

    public function testCreateFromGlobalsWithUploadedFileInstance(): void
    {
        $files = ['file' => UploadedFileCreator::create($this->tmpFile, 1024, UPLOAD_ERR_OK)];
        $this->assertSame($files, UploadedFileCreator::createFromGlobals($files));
    }


    public function testCreateFromGlobalsOneFile(): void
    {
        $files = [
            'file' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
                'tmp_name' => $this->tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024,
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['file']);
        $this->assertSame($files['file']['name'], $uploadedFiles['file']->getClientFilename());
        $this->assertSame($files['file']['type'], $uploadedFiles['file']->getClientMediaType());
        $this->assertSame($files['file']['tmp_name'], $uploadedFiles['file']->getStream()->getMetadata('uri'));
        $this->assertSame($files['file']['error'], $uploadedFiles['file']->getError());
        $this->assertSame($files['file']['size'], $uploadedFiles['file']->getSize());
    }

    public function testCreateFromGlobalsNestedOneFile(): void
    {
        $files = [
            'file' => [
                [
                    'name' => 'file.txt',
                    'type' => 'text/plain',
                    'tmp_name' => $this->tmpFile,
                    'error' => UPLOAD_ERR_OK,
                    'size' => 1024,
                ],
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['file'][0]);
        $this->assertSame($files['file'][0]['name'], $uploadedFiles['file'][0]->getClientFilename());
        $this->assertSame($files['file'][0]['type'], $uploadedFiles['file'][0]->getClientMediaType());
        $this->assertSame($files['file'][0]['tmp_name'], $uploadedFiles['file'][0]->getStream()->getMetadata('uri'));
        $this->assertSame($files['file'][0]['error'], $uploadedFiles['file'][0]->getError());
        $this->assertSame($files['file'][0]['size'], $uploadedFiles['file'][0]->getSize());
    }

    public function testCreateFromGlobalsLinearMultipleNotStructuredFiles(): void
    {
        $files = [
            'files' => [
                'name' => [
                    'file_1' => 'file.txt',
                    'file_2' => 'image.png',
                ],
                'type' => [
                    'file_1' => 'text/plain',
                    'file_2' => 'image/png',
                ],
                'tmp_name' => [
                    'file_1' => $this->tmpFile,
                    'file_2' => $this->tmpFile,
                ],
                'error' => [
                    'file_1' => UPLOAD_ERR_OK,
                    'file_2' => UPLOAD_ERR_OK,
                ],
                'size' => [
                    'file_1' => 1024,
                    'file_2' => 98174,
                ],
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        for ($k = 1; $k <= 2; $k++) {
            $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['files']['file_' . $k]);
            $this->assertSame(
                $files['files']['name']['file_' . $k],
                $uploadedFiles['files']['file_' . $k]->getClientFilename()
            );
            $this->assertSame(
                $files['files']['type']['file_' . $k],
                $uploadedFiles['files']['file_' . $k]->getClientMediaType()
            );
            $this->assertSame(
                $files['files']['tmp_name']['file_' . $k],
                $uploadedFiles['files']['file_' . $k]->getStream()->getMetadata('uri')
            );
            $this->assertSame(
                $files['files']['error']['file_' . $k],
                $uploadedFiles['files']['file_' . $k]->getError()
            );
            $this->assertSame(
                $files['files']['size']['file_' . $k],
                $uploadedFiles['files']['file_' . $k]->getSize()
            );
        }
    }

    public function testCreateFromGlobalsNestedMultipleNotStructuredFiles(): void
    {
        $files = [
            'files' => [
                'name' => [
                    'data' => [
                        'nested' => [
                            'file_1' => 'file.txt',
                            'file_2' => 'image.png',
                        ],
                    ],
                ],
                'type' => [
                    'data' => [
                        'nested' => [
                            'file_1' => 'text/plain',
                            'file_2' => 'image/png',
                        ],
                    ],
                ],
                'tmp_name' => [
                    'data' => [
                        'nested' => [
                            'file_1' => $this->tmpFile,
                            'file_2' => $this->tmpFile,
                        ],
                    ],
                ],
                'error' => [
                    'data' => [
                        'nested' => [
                            'file_1' => UPLOAD_ERR_OK,
                            'file_2' => UPLOAD_ERR_OK,
                        ],
                    ],
                ],
                'size' => [
                    'data' => [
                        'nested' => [
                            'file_1' => 1024,
                            'file_2' => 98174,
                        ],
                    ],
                ],
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        for ($k = 1; $k <= 2; $k++) {
            $this->assertInstanceOf(
                UploadedFileInterface::class,
                $uploadedFiles['files']['data']['nested']['file_' . $k]
            );
            $this->assertSame(
                $files['files']['name']['data']['nested']['file_' . $k],
                $uploadedFiles['files']['data']['nested']['file_' . $k]->getClientFilename()
            );
            $this->assertSame(
                $files['files']['type']['data']['nested']['file_' . $k],
                $uploadedFiles['files']['data']['nested']['file_' . $k]->getClientMediaType()
            );
            $this->assertSame(
                $files['files']['tmp_name']['data']['nested']['file_' . $k],
                $uploadedFiles['files']['data']['nested']['file_' . $k]->getStream()->getMetadata('uri')
            );
            $this->assertSame(
                $files['files']['error']['data']['nested']['file_' . $k],
                $uploadedFiles['files']['data']['nested']['file_' . $k]->getError()
            );
            $this->assertSame(
                $files['files']['size']['data']['nested']['file_' . $k],
                $uploadedFiles['files']['data']['nested']['file_' . $k]->getSize()
            );
        }
    }

    public function testCreateFromGlobalsMultipleStructuredFiles(): void
    {
        $files = [
            'file_1' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
                'tmp_name' => $this->tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024,
            ],
            'file_2' => [
                'name' => 'image.png',
                'type' => 'image/png',
                'tmp_name' => $this->tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 98760,
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        for ($k = 1; $k <= 2; $k++) {
            $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['file_' . $k]);
            $this->assertSame($files['file_' . $k]['name'], $uploadedFiles['file_' . $k]->getClientFilename());
            $this->assertSame($files['file_' . $k]['type'], $uploadedFiles['file_' . $k]->getClientMediaType());
            $this->assertSame(
                $files['file_' . $k]['tmp_name'],
                $uploadedFiles['file_' . $k]->getStream()->getMetadata('uri')
            );
            $this->assertSame($files['file_' . $k]['error'], $uploadedFiles['file_' . $k]->getError());
            $this->assertSame($files['file_' . $k]['size'], $uploadedFiles['file_' . $k]->getSize());
        }
    }

    public function testCreateFromGlobalsMultipleNestedStructuredFiles(): void
    {
        $files = [
            'file_1' => [
                'name' => ['file.txt', 'image.png'],
                'type' => ['text/plain', 'image/png'],
                'tmp_name' => [$this->tmpFile, $this->tmpFile],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [1024, 98760],
            ],
            'file_2' => [
                'name' => ['audio.mp3', 'video.mp4'],
                'type' => ['audio/x-mpeg-3', 'video/mp4'],
                'tmp_name' => [$this->tmpFile, $this->tmpFile],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [10245678, 98766432],
            ],
        ];

        $uploadedFiles = UploadedFileCreator::createFromGlobals($files);

        for ($k = 1; $k <= 2; $k++) {
            for ($i = 0; $i <= 1; $i++) {
                $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFiles['file_' . $k][$i]);
                $this->assertSame(
                    $files['file_' . $k]['name'][$i],
                    $uploadedFiles['file_' . $k][$i]->getClientFilename()
                );
                $this->assertSame(
                    $files['file_' . $k]['type'][$i],
                    $uploadedFiles['file_' . $k][$i]->getClientMediaType()
                );
                $this->assertSame(
                    $files['file_' . $k]['tmp_name'][$i],
                    $uploadedFiles['file_' . $k][$i]->getStream()->getMetadata('uri')
                );
                $this->assertSame(
                    $files['file_' . $k]['error'][$i],
                    $uploadedFiles['file_' . $k][$i]->getError()
                );
                $this->assertSame(
                    $files['file_' . $k]['size'][$i],
                    $uploadedFiles['file_' . $k][$i]->getSize()
                );
            }
        }
    }

    /**
     * @return array
     */
    public function invalidFileDataProvider(): array
    {
        return [
            'array-value-null' => [[null]],
            'array-value-true' => [[true]],
            'array-value-false' => [[false]],
            'array-value-int' => [[1]],
            'array-value-float' => [[1.1]],
            'array-value-string' => [['string']],
            'array-value-object' => [[new StdClass()]],
            'array-value-callable' => [[fn() => null]],
            'one-dimensional-array-without-key' => [
                [
                    'name' => 'file.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/phpN3FmFr',
                    'error' => UPLOAD_ERR_OK,
                    'size' => 1024,
                ],
            ],
            'multidimensional-not-structured-array-without-key' => [
                [
                    'name' => [
                        'file_1' => 'file.txt',
                        'file_2' => 'image.png',
                    ],
                    'type' => [
                        'file_1' => 'text/plain',
                        'file_2' => 'image/png',
                    ],
                    'tmp_name' => [
                        'file_1' => '/tmp/phpN3FmFr',
                        'file_2' => '/tmp/phpLs7DaJ',
                    ],
                    'error' => [
                        'file_1' => UPLOAD_ERR_OK,
                        'file_2' => UPLOAD_ERR_OK,
                    ],
                    'size' => [
                        'file_1' => 1024,
                        'file_2' => 98174,
                    ],
                ],
            ],
            'not-passed-tmp_name' => [
                [
                    'file' => [
                        'name' => 'file.txt',
                        'type' => 'text/plain',
                        'error' => UPLOAD_ERR_OK,
                        'size' => 1024,
                    ],
                ],
            ],
            'multidimensional-not-passed-tmp_name' => [
                [
                    'file' => [
                        'name' => [
                            'file_1' => 'file.txt',
                            'file_2' => 'image.png',
                        ],
                        'type' => [
                            'file_1' => 'text/plain',
                            'file_2' => 'image/png',
                        ],
                        'error' => [
                            'file_1' => UPLOAD_ERR_OK,
                            'file_2' => UPLOAD_ERR_OK,
                        ],
                        'size' => [
                            'file_1' => 1024,
                            'file_2' => 98174,
                        ],
                    ],
                ],
            ],
            'not-passed-error' => [
                [
                    'file' => [
                        'name' => 'file.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/phpN3FmFr',
                        'size' => 1024,
                    ],
                ],
            ],
            'multidimensional-not-passed-error' => [
                [
                    'file' => [
                        'name' => [
                            'file_1' => 'file.txt',
                            'file_2' => 'image.png',
                        ],
                        'type' => [
                            'file_1' => 'text/plain',
                            'file_2' => 'image/png',
                        ],
                        'tmp_name' => [
                            'file_1' => '/tmp/phpN3FmFr',
                            'file_2' => '/tmp/phpLs7DaJ',
                        ],
                        'size' => [
                            'file_1' => 1024,
                            'file_2' => 98174,
                        ],
                    ],
                ],
            ],
            'not-passed-size' => [
                [
                    'file' => [
                        'name' => 'file.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/phpN3FmFr',
                        'error' => UPLOAD_ERR_OK,
                    ],
                ],
            ],
            'multidimensional-not-passed-size' => [
                [
                    'file' => [
                        'name' => [
                            'file_1' => 'file.txt',
                            'file_2' => 'image.png',
                        ],
                        'type' => [
                            'file_1' => 'text/plain',
                            'file_2' => 'image/png',
                        ],
                        'tmp_name' => [
                            'file_1' => '/tmp/phpN3FmFr',
                            'file_2' => '/tmp/phpLs7DaJ',
                        ],
                        'error' => [
                            'file_1' => UPLOAD_ERR_OK,
                            'file_2' => UPLOAD_ERR_OK,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidFileDataProvider
     * @param mixed $fileData
     */
    public function testCreateFromGlobalsThrowExceptionForInvalidFileData($fileData): void
    {
        $this->expectException(InvalidArgumentException::class);
        UploadedFileCreator::createFromGlobals($fileData);
    }
}

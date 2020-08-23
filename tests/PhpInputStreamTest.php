<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ServerRequest;

use HttpSoft\ServerRequest\PhpInputStream;
use RuntimeException;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function fopen;
use function stream_get_meta_data;
use function substr;

class PhpInputStreamTest extends TestCase
{
    /**
     * @var string
     */
    private string $file;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var PhpInputStream
     */
    private PhpInputStream $stream;

    public function setUp(): void
    {
        $this->file = __DIR__ . '/TestAsset/php-input-stream.txt';
        $this->resource = fopen($this->file, 'r');
        $this->stream = new PhpInputStream($this->file);
    }

    public function testGetDefault(): void
    {
        $stream = new PhpInputStream();
        $this->assertSame(0, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertSame('', $stream->getContents());
        $this->assertSame('php://input', $stream->getMetadata('uri'));
    }

    public function testGetMetadata()
    {
        $this->assertSame('plainfile', $this->stream->getMetadata('wrapper_type'));
        $this->assertSame('STDIO', $this->stream->getMetadata('stream_type'));
        $this->assertSame('r', $this->stream->getMetadata('mode'));
        $this->assertSame(0, $this->stream->getMetadata('unread_bytes'));
        $this->assertSame(true, $this->stream->getMetadata('seekable'));
        $this->assertSame($this->file, $this->stream->getMetadata('uri'));
    }

    public function testCloseAndGetSizeIfUnknown(): void
    {
        $this->stream->close();
        $this->assertNull($this->stream->getSize());
    }

    public function testDetach(): void
    {
        $stream = new PhpInputStream($this->resource);
        $this->assertSame(stream_get_meta_data($this->resource), stream_get_meta_data($stream->detach()));
        $this->assertNull($stream->getSize());
    }

    public function testIsSeekableReturnTrue(): void
    {
        $stream = new PhpInputStream($this->file);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnFalse(): void
    {
        $stream = new PhpInputStream($this->file);
        $stream->close();
        $this->assertFalse($stream->isSeekable());
    }

    public function testTellThrowExceptionForInvalidResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->stream->close();
        $this->stream->tell();
    }

    public function testSeekThrowExceptionForInvalidResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->stream->close();
        $this->stream->seek(1);
    }

    public function testReadThrowExceptionForInvalidResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->stream->close();
        $this->stream->read(1);
    }

    public function testGetContentsThrowExceptionForInvalidResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->stream->close();
        $this->stream->getContents();
    }

    public function testIsWritableAlwaysReturnFalse()
    {
        $this->assertFalse($this->stream->isWritable());
    }

    public function testReadPartsInLoop()
    {
        $content = '';
        while (!$this->stream->eof()) {
            $content .= $this->stream->read(64);
        }
        $this->assertSame(file_get_contents($this->file), $content);
    }

    public function testGetContentsReturnRemainingContents()
    {
        $this->stream->read($length = 64);
        $this->assertSame(
            substr(file_get_contents($this->file), $length),
            $this->stream->getContents()
        );

        $stream = new PhpInputStream('data://,123');
        $this->assertSame('123', $stream->getContents());

        $stream = new PhpInputStream('data://,123');
        $stream->read(1);
        $this->assertSame('23', $stream->getContents());
    }

    public function testToStringAlwaysReturnFullContent()
    {
        $this->stream->read(64);
        $this->assertSame(file_get_contents($this->file), $this->stream->__toString());

        $first = (string) $this->stream;
        $this->stream->read(64);
        $second = (string) $this->stream;
        $this->stream->read(64);
        $third = (string) $this->stream;
        $this->assertSame($first, $second);
        $this->assertSame($first, $third);
        $this->assertSame($second, $third);
    }
}

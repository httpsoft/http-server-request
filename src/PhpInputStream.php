<?php

declare(strict_types=1);

namespace HttpSoft\ServerRequest;

use HttpSoft\Message\StreamTrait;
use Psr\Http\Message\StreamInterface;

final class PhpInputStream implements StreamInterface
{
    use StreamTrait {
        read as private readInternal;
        getContents as private getContentsInternal;
    }

    /**
     * @var string
     */
    private string $cache = '';

    /**
     * @var bool
     */
    private bool $isEof = false;

    /**
     * @param string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        $this->init($stream, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if (!$this->isEof) {
            $this->getContents();
        }

        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $result = $this->readInternal($length);

        if (!$this->isEof) {
            $this->cache .= $result;
        }

        if ($this->eof()) {
            $this->isEof = true;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if ($this->isEof) {
            return $this->cache;
        }

        $result = $this->getContentsInternal();
        $this->cache .= $result;
        $this->isEof = true;

        return $result;
    }
}

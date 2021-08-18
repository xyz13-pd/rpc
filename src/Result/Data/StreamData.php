<?php

namespace inisire\CQRS\Result\Data;

use Psr\Http\Message\StreamInterface;

class StreamData extends AbstractData
{
    public function __construct(
        private StreamInterface $stream,
        private string $mimeType
    ) {}
    
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
    
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
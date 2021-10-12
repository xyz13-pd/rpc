<?php

namespace inisire\RPC\Result;

use inisire\RPC\Http\Headers;
use Psr\Http\Message\StreamInterface;

class FileStreamResult extends Result
{
    private StreamInterface $stream;

    public function __construct(StreamInterface $stream, string $mimeType)
    {
        parent::__construct(null);
        $this->stream = $stream;

        $this->getMetadata()->join(Headers::createForItem('Content-Type', $mimeType));
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}
<?php

namespace inisire\CQRS\Result;

use inisire\CQRS\Http\StatusCode;
use inisire\CQRS\Result\Metadata\Metadata;

class Result implements SuccessResultInterface
{
    private Metadata $metadata;

    private mixed $data = null;

    public function __construct(mixed $data)
    {
        $this->data = $data;
        $this->metadata = new Metadata();
        $this->metadata
            ->join(new StatusCode(200));
    }

    public function getData(string $class = null)
    {
        if ($class === null || $this->data instanceof $class) {
            return $this->data;
        } else {
            return null;
        }
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getExitCode(): int
    {
        return 0;
    }
}
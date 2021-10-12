<?php

namespace inisire\RPC\Result;

use inisire\RPC\Http\StatusCode;
use inisire\RPC\Result\Metadata\Metadata;

/**
 * @template T
 */
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

    /**
     * @param string|null $class
     *
     * @return T
     */
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
<?php

namespace inisire\RPC\Error;


use inisire\RPC\Http\Headers;
use inisire\RPC\Http\StatusCode;
use inisire\RPC\Result\ErrorResultInterface;
use inisire\RPC\Result\Result;

abstract class HttpError extends Result implements ErrorResultInterface
{
    public function __construct()
    {
        parent::__construct(null);

        $this->getMetadata()
             ->join(new StatusCode($this->getHttpCode()))
             ->join(new Headers($this->getHttpHeaders()));
    }

    public function getHttpHeaders(): array
    {
        return [];
    }

    abstract public function getHttpCode(): int;
}
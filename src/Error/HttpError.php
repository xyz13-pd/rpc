<?php

namespace inisire\CQRS\Error;


use inisire\CQRS\Http\Headers;
use inisire\CQRS\Http\StatusCode;
use inisire\CQRS\Result\ErrorResultInterface;
use inisire\CQRS\Result\Result;

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
<?php

namespace inisire\RPC\Error;


use inisire\DataObject\Error\ErrorMessage;
use inisire\RPC\Http\HttpResultInterface;
use inisire\RPC\Result\ResultInterface;


class Unauthorized implements ErrorInterface, ResultInterface, HttpResultInterface
{
    public function getCode(): string
    {
        return '1ec00efa-d334-6128-9d0e-05046e394a77';
    }

    public function getMessage(): ErrorMessage
    {
        return new ErrorMessage('Unauthorized');
    }

    public function getHttpCode(): int
    {
        return 401;
    }

    public function getHttpHeaders(): array
    {
        return [];
    }

    public function getOutput(): mixed
    {
        return null;
    }
}
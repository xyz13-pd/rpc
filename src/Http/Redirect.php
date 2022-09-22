<?php

namespace inisire\RPC\Http;

use inisire\DataObject\Error\ErrorMessage;
use inisire\RPC\Http\HttpResultInterface;
use inisire\RPC\Result\ResultInterface;

class Redirect implements ResultInterface, HttpResultInterface
{
    public function getHttpHeaders(): array
    {
        return [];
    }

    public function getHttpCode(): int
    {
        return 301;
    }

    public function getOutput(): mixed
    {
        return null;
    }
}
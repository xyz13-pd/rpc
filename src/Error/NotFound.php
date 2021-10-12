<?php

namespace inisire\RPC\Error;


use inisire\DataObject\Error\ErrorMessage;

class NotFound extends HttpError
{
    public function getCode(): string
    {
        return '1ec00ef8-d2f8-6116-a101-b969ba46bac1';
    }

    public function getMessage(): ErrorMessage
    {
        return new ErrorMessage('Not found');
    }

    public function getHttpCode(): int
    {
        return 404;
    }
}
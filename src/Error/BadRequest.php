<?php

namespace inisire\RPC\Error;

use inisire\DataObject\Error\ErrorMessage;

class BadRequest extends HttpError
{
    public function getCode(): string
    {
        return '1ec03457-6a38-6f5c-a57e-57277a6c17f6';
    }

    public function getMessage(): ErrorMessage
    {
        return new ErrorMessage('Bad request');
    }

    public function getHttpCode(): int
    {
        return 400;
    }
}
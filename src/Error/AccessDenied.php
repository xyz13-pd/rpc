<?php

namespace inisire\CQRS\Error;

use inisire\CQRS\Result\ErrorResultInterface;
use inisire\DataObject\Error\ErrorMessage;

class AccessDenied extends HttpError implements ErrorResultInterface
{
    public function getCode(): string
    {
        return '1ec00efb-17f9-6eb6-a1d9-55e2cc62c6ce';
    }

    public function getMessage(): ErrorMessage
    {
        return new ErrorMessage('Access denied');
    }

    public function getHttpCode(): int
    {
        return 403;
    }
}
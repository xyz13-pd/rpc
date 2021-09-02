<?php

namespace inisire\CQRS\Error;

use inisire\DataObject\Error\ErrorMessage;

class DomainError extends BadRequest
{
    private string $code;
    private ErrorMessage $message;

    public function __construct(string $code, string $message)
    {
        parent::__construct();
        $this->code = $code;
        $this->message = new ErrorMessage($message);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): ErrorMessage
    {
        return $this->message;
    }
}
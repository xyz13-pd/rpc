<?php

namespace inisire\CQRS\Http;

use inisire\CQRS\Result\Metadata\Metadata;

class StatusCode extends Metadata
{
    public function __construct(int $value)
    {
        parent::__construct(['http.statusCode' => $value]);
    }
}
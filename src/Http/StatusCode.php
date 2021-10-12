<?php

namespace inisire\RPC\Http;

use inisire\RPC\Result\Metadata\Metadata;

class StatusCode extends Metadata
{
    public function __construct(int $value)
    {
        parent::__construct(['http.statusCode' => $value]);
    }
}
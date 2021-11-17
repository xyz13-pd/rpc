<?php

namespace inisire\RPC\Result;

use inisire\RPC\Http\StatusCode;

class EmptyResult extends Result
{
    public function __construct()
    {
        parent::__construct(null);
        $this->getMetadata()->join(new StatusCode(204));
    }
}
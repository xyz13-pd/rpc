<?php

namespace inisire\RPC\Schema;

class Stream extends Schema
{
    public function getContentType(): ?string
    {
        return 'application/octet-stream';
    }
}
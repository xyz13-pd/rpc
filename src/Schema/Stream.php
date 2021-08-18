<?php

namespace inisire\CQRS\Schema;

class Stream extends Schema
{
    public function getContentType(): ?string
    {
        return 'application/octet-stream';
    }
}
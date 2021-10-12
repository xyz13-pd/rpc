<?php

namespace inisire\RPC\Schema;

use inisire\DataObject\Definition\Definition;

class Json extends Schema
{
    public Definition $schema;

    public function __construct(Definition $schema)
    {
        $this->schema = $schema;
    }

    public function getContentType(): ?string
    {
        return 'application/json';
    }
}
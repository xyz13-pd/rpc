<?php

namespace inisire\CQRS\Schema;

use inisire\DataObject\Definition\Definition;

class FormData extends Schema
{
    public Definition $schema;

    public bool $multipart;

    public function __construct(Definition $schema, bool $multipart = false)
    {
        $this->schema = $schema;
        $this->multipart = $multipart;
    }

    public function getContentType(): ?string
    {
        return $this->multipart
            ? 'multipart/form-data'
            : 'application/x-www-form-urlencoded';
    }
}
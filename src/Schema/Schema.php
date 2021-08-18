<?php

namespace inisire\CQRS\Schema;

abstract class Schema
{
    abstract public function getContentType(): ?string;
}
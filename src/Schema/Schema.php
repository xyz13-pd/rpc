<?php

namespace inisire\RPC\Schema;

abstract class Schema
{
    abstract public function getContentType(): ?string;
}
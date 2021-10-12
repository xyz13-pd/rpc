<?php

namespace inisire\RPC\Http;

use inisire\RPC\Result\Metadata\Metadata;

class Headers extends Metadata
{
    public function __construct(array $items)
    {
        parent::__construct(['http.headers' => $items]);
    }

    public static function createForItem(string $name, string $value)
    {
        return new static([$name => $value]);
    }
}
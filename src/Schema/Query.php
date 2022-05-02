<?php

namespace inisire\RPC\Schema;

#[\Attribute]
class Query extends Entrypoint
{
    public array $methods = ['GET'];
}
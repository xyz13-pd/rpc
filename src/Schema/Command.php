<?php

namespace inisire\RPC\Schema;

#[\Attribute]
class Command extends Entrypoint
{
    public array $methods = ['POST'];


}
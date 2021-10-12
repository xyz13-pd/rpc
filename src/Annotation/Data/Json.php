<?php

namespace inisire\RPC\Annotation\Data;


/**
 * @Annotation 
 */
class Json extends \inisire\RPC\Schema\Json
{
    public function __construct($options)
    {
        parent::__construct($options['schema']);
    }
}
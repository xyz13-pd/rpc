<?php

namespace inisire\CQRS\Annotation\Data;


/**
 * @Annotation 
 */
class Json extends \inisire\CQRS\Schema\Json
{
    public function __construct($options)
    {
        parent::__construct($options['schema']);
    }
}
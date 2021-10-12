<?php

namespace inisire\RPC\Annotation\Data;

/**
 * @Annotation 
 */
class FormData extends \inisire\RPC\Schema\FormData
{
    public function __construct($options)
    {
        parent::__construct(
            $options['schema'],
            $options['multipart'] ?? false
        );
    }
}
<?php

namespace inisire\CQRS\Annotation\Data;

/**
 * @Annotation 
 */
class FormData extends \inisire\CQRS\Schema\FormData
{
    public function __construct($options)
    {
        parent::__construct(
            $options['schema'],
            $options['multipart'] ?? false
        );
    }
}
<?php


namespace inisire\CQRS\Annotation;

use inisire\DataObject\Definition\Definition;

/**
 * @Annotation
 */
class Command extends RPC
{
    public array $methods = ['POST'];
}
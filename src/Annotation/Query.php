<?php


namespace inisire\CQRS\Annotation;


use inisire\DataObject\Definition\Definition;

/**
 * @Annotation
 */
class Query extends RPC
{
    public array $methods = ['GET'];
}
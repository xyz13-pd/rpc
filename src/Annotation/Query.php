<?php


namespace inisire\CQRS\Annotation;


use inisire\DataObject\Definition\Definition;

/**
 * @Annotation
 */
class Query
{
    public string $path;
    public Definition $input;
    public Definition $output;
    public array $tags = [];
    public string $description = "";
    public array $methods = ['GET'];
}
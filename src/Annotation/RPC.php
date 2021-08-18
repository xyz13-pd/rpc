<?php


namespace inisire\CQRS\Annotation;

use inisire\CQRS\Schema\Schema;

/**
 * @Annotation
 */
class RPC
{
    public string $path;
    public Schema $input;
    public Schema $output;
    public array $tags = [];
    public string $description = "";
    public array $methods = ['GET'];
}
<?php


namespace inisire\RPC\Annotation;

use inisire\RPC\Schema\Schema;

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
<?php

namespace inisire\RPC\Schema;

#[\Attribute]
class Entrypoint
{
    public string $path;
    public Data $input;
    public Data $output;
    public array $tags = [];
    public string $description = "";
    public array $methods = ['GET'];

    public function __construct(string $path, Data $input, Data $output, array $tags = [], string $description = '', array $methods = [])
    {
        $this->path = $path;
        $this->input = $input;
        $this->output = $output;
        $this->tags = $tags;
        $this->description = $description;
        $this->methods = $methods;
    }
}
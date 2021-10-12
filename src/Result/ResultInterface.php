<?php


namespace inisire\RPC\Result;


use inisire\RPC\Result\Metadata\Metadata;


interface ResultInterface
{
    public function getMetadata(): Metadata;

    public function getExitCode(): int;

    /**
     * @template A
     *
     * @param ?class-string<A> $class
     *
     * @return ?A
     */
    public function getData(string $class = null);
}
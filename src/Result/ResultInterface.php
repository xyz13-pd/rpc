<?php


namespace inisire\CQRS\Result;


use inisire\CQRS\Result\Metadata\Metadata;


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
<?php


namespace inisire\CQRS\Result;


use inisire\DataObject\Error\ErrorInterface;

interface ResultInterface
{
    public function getData();

    /**
     * @return array<ErrorInterface>
     */
    public function getErrors(): iterable;
}
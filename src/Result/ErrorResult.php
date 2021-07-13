<?php


namespace inisire\CQRS\Result;


use inisire\DataObject\Error\ErrorInterface;

class ErrorResult implements ResultInterface
{
    /**
     * @var array<ErrorInterface>
     */
    private iterable $errors;

    public function __construct(iterable $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): iterable
    {
        return $this->errors;
    }
}
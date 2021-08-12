<?php


namespace inisire\CQRS\Result;

/**
 * @template T
 */
class SuccessResult implements ResultInterface
{
    private mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    /**
     * @return T
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    public function getErrors(): iterable
    {
        return [];
    }
}
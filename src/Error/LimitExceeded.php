<?php

namespace inisire\RPC\Error;

use inisire\DataObject\Error\ErrorMessage;

class LimitExceeded extends BadRequest
{
    private ?int $count;
    private ?int $limit;

    public function __construct(int $count = null, int $limit = null)
    {
        $this->count = $count;
        $this->limit = $limit;
        parent::__construct();
    }

    public function getCode(): string
    {
        return '1ec0448d-4ca0-680a-b256-b91e52028005';
    }

    public function getMessage(): ErrorMessage
    {
        return new ErrorMessage('Too many requests');
    }

    public function getHttpCode(): int
    {
        return 429;
    }

    public function getHttpHeaders(): array
    {
        $headers = [];

        if ($this->limit !== null && $this->count !== null) {
            $headers['X-Rate-Limit-Remaining'] = $this->limit - $this->count;
        }

        if ($this->limit !== null) {
            $headers['X-Rate-Limit-Limit'] = $this->limit;
        }

        return $headers;
    }
}
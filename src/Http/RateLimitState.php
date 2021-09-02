<?php

namespace inisire\CQRS\Http;


class RateLimitState extends Headers
{
    public function __construct(int $limit, int $remaining, \DateTime $reset = null)
    {
        $headers['X-Rate-Limit-Limit'] = $limit;
        $headers['X-Rate-Limit-Remaining'] = $remaining;

        if ($reset !== null) {
            $headers['X-Rate-Limit-Reset'] = $reset->getTimestamp();
        }

        parent::__construct($headers);
    }
}
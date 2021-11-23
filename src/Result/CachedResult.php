<?php

namespace inisire\RPC\Result;

use inisire\RPC\Http\Headers;

class CachedResult extends Result
{
    const MODE_PUBLIC = 'public';
    const MODE_PRIVATE = 'private';
    const OPTION_MAX_AGE = 'max-age';
    const OPTION_IMMUTABLE = 'immutable';

    public function __construct(mixed $data, string $mode = self::MODE_PUBLIC, array $options = [])
    {
        parent::__construct($data);

        $parts = [$mode];

        foreach ($options as $key => $value) {
            if ($value === true) {
                $parts[] = $key;
            } else {
                $parts[] = sprintf('%s=%s', $key, $value);
            }
        }

        $this->getMetadata()->join(new Headers([
            'Cache-Control' => implode(', ', $parts)
        ]));
    }
}
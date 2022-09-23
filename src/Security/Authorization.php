<?php

namespace inisire\RPC\Security;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Authorization
{
    public function __construct(
        private readonly array $roles = []
    )
    {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
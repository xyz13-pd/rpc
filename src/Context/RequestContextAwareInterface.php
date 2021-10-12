<?php

namespace inisire\RPC\Context;

use Symfony\Component\HttpFoundation\Request;

interface RequestContextAwareInterface
{
    public function applyContext(Request $request): void;
}
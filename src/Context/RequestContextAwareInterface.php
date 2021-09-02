<?php

namespace inisire\CQRS\Context;

use Symfony\Component\HttpFoundation\Request;

interface RequestContextAwareInterface
{
    public function applyContext(Request $request): void;
}
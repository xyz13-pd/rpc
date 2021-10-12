<?php

namespace inisire\RPC\Result\Metadata;

class Metadata
{
    private array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getCollection(string $key): array
    {
        return (array) $this->items[$key] ?? [];
    }

    public function getString(string $key): ?string
    {
        return (string) $this->items[$key] ?? null;
    }

    public function setCollection(string $key, array $collection)
    {
        $this->items[$key] = $collection;
    }

    public function setString(string $key, string $string)
    {
        $this->items[$key] = $string;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function join(Metadata $metadata): self
    {
        $this->items = array_merge($this->items, $metadata->toArray());

        return $this;
    }
}
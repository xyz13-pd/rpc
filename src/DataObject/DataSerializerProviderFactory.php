<?php

namespace inisire\RPC\DataObject;

use inisire\DataObject\DataSerializerProvider;
use inisire\DataObject\Serializer\CollectionSerializer;
use inisire\DataObject\Serializer\DateTimeSerializer;
use inisire\DataObject\Serializer\DictionarySerializer;
use inisire\DataObject\Serializer\FileSerializer;
use inisire\DataObject\Serializer\ObjectReferenceSerializer;
use inisire\DataObject\Serializer\ObjectSerializer;
use inisire\DataObject\Serializer\ScalarSerializer;
use Symfony\Component\DependencyInjection\ServiceLocator;

class DataSerializerProviderFactory
{
    public function __construct(
        private ServiceLocator $locator
    )
    {
    }

    public function create(): DataSerializerProvider
    {
        $loaders = [];
        foreach ($this->locator->getProvidedServices() as $name) {
            $loaders[] = $this->locator->get($name);
        }

        $provider = new DataSerializerProvider();
        $provider->add([
            new ScalarSerializer(),
            new DictionarySerializer(),
            new DateTimeSerializer(),
            new FileSerializer(),
            new ObjectSerializer($provider),
            new CollectionSerializer($provider),
            new ObjectReferenceSerializer($loaders)
        ]);


        return $provider;
    }
}
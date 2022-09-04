<?php

namespace inisire\RPC\Entrypoint;

use inisire\RPC\Schema as Schema;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntrypointRegistry
{
    public function __construct(
        private ServiceLocator     $container,
        private ValidatorInterface $validator
    )
    {
    }

    public function getEntrypoint(string $name): ?Entrypoint
    {
        foreach ($this->getEntrypoints() as $entrypoint) {
            if ($name !== $entrypoint->getName()) {
                continue;
            }

            return $entrypoint;
        }

        return null;
    }

    /**
     * @return iterable<Entrypoint>
     */
    public function getEntrypoints(): iterable
    {
        foreach ($this->container->getProvidedServices() as $id) {
            if (!$this->container->has($id)) {
                continue;
            }

            $instance = $this->container->get($id);

            $reflection = new \ReflectionClass($instance);

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(Schema\Entrypoint::class) as $attribute) {
                    /**
                     * @var Schema\Entrypoint $entrypoint
                     */
                    $entrypoint = $attribute->newInstance();

                    yield new Entrypoint(
                        $entrypoint->name,
                        $entrypoint->input,
                        $entrypoint->output,
                        $entrypoint->description,
                        $instance,
                        $method->getName(),
                        $this->validator
                    );
                }
            }
        }
    }
}
<?php

namespace inisire\RPC\Entrypoint;

use inisire\RPC\Schema as Schema;
use inisire\RPC\Security\Authorization;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntrypointRegistry
{
    public function __construct(
        private ServiceLocator     $container,
        private ValidatorInterface $validator,
        private Security           $security
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

                    $authorization = $method->getAttributes(Authorization::class)[0] ?? null;

                    yield new Entrypoint(
                        $entrypoint->name,
                        $entrypoint->input,
                        $entrypoint->output,
                        $entrypoint->description,
                        $instance,
                        $method->getName(),
                        $authorization?->newInstance(),
                        $this->validator,
                        $this->security
                    );
                }
            }
        }
    }
}
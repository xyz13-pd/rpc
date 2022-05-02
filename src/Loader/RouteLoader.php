<?php


namespace inisire\RPC\Loader;


use inisire\RPC\Controller\BusBridgeController;
use inisire\RPC\Schema\Entrypoint;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader implements RouteLoaderInterface
{
    private ServiceLocator $container;

    public function __construct(ServiceLocator $container, string $env = null)
    {
        parent::__construct($env);
        $this->container = $container;
    }

    public function __invoke()
    {
        return new RouteCollection();
    }

    public function load($resource, string $type = null)
    {
        $collection = new RouteCollection();

        foreach ($this->container->getProvidedServices() as $id) {
            if (!$this->container->has($id)) {
                continue;
            }

            $instance = $this->container->get($id);

            $reflection = new \ReflectionClass($instance);

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(Entrypoint::class) as $attribute) {
                    /**
                     * @var Entrypoint $entrypoint
                     */
                    $entrypoint = $attribute->newInstance();

                    $name = $entrypoint->path;
                    $defaults = [
                        '_controller' => BusBridgeController::class,
                        '_command_handler' => [$method->class, $method->name],
                        '_schema' => serialize($entrypoint)
                    ];
                    $methods = $entrypoint->methods;

                    $collection->add($name, new Route($entrypoint->path, $defaults, [], [], null, [], $methods));
                }
            }
        }

        return $collection;
    }

    public function supports($resource, string $type = null)
    {
        return $type === 'rpc';
    }
}
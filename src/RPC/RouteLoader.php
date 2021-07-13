<?php


namespace inisire\CQRS\RPC;


use Doctrine\Common\Annotations\AnnotationReader;
use inisire\CQRS\Annotation\Command;
use inisire\CQRS\Annotation\Query;
use inisire\CQRS\Controller\BusBridgeController;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader implements RouteLoaderInterface
{
    private ServiceLocator $container;
    private AnnotationReader $annotationReader;

    public function __construct(ServiceLocator $container)
    {
        $this->container = $container;
        $this->annotationReader = new AnnotationReader();
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

            $annotations = [];
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($this->annotationReader->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof Query || $annotation instanceof Command) {
                        $annotations[] = $annotation;
                    }
                }
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof Query) {
                    $name = $annotation->name ?? $annotation->path;
                    $defaults = [
                        '_controller' => BusBridgeController::class,
                        '_schema' => serialize($annotation)
                    ];
                    $method = 'GET';
                } elseif ($annotation instanceof Command) {
                    $name = $annotation->name ?? $annotation->path;
                    $defaults = [
                        '_controller' => BusBridgeController::class,
                        '_schema' => serialize($annotation)
                    ];
                    $method = 'POST';
                } else {
                    continue;
                }

                $collection->add($name, new Route($annotation->path, $defaults, [], [], null, [], [$method]));
            }
        }

        return $collection;
    }

    public function supports($resource, string $type = null)
    {
        return $type === 'cqrs';
    }
}
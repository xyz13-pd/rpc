<?php


namespace inisire\CQRS\Command;


use inisire\CQRS\Annotation\RPC;
use inisire\DataObject\Util\DocGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;


class OpenApiGenerateCommand extends Command
{
    protected static $defaultName = 'inisire:openapi:generate';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->router->getRouteCollection();

        $generator = new DocGenerator();

        foreach ($routes->getIterator() as $name => $route) {
            foreach ($route->getMethods() as $method) {
                /**
                 * @var RPC $schema
                 */
                $schema = unserialize($route->getDefault('_schema'));

                if (!$schema) {
                    continue;
                }

                $generator->addPath($method, $route->getPath(), $schema->input, $schema->output, $schema->tags, $schema->description);
            }
        }

        $content = json_encode($generator->getDoc(), JSON_PRETTY_PRINT);
        file_put_contents('swagger.json', $content);

        echo $content . PHP_EOL;

        return 0;
    }
}
<?php


namespace inisire\RPC\Command;


use inisire\DataObject\OpenAPI\RequestSchema;
use inisire\DataObject\OpenAPI\ResponseSchema;
use inisire\DataObject\OpenAPI\SpecificationBuilder;
use inisire\RPC\Schema\Entrypoint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'rpc:documentation:generate',
    description: 'Generate OpenAPI schema for RPCs'
)]
class DocumentationGenerateCommand extends Command
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    protected function configure()
    {
        $this->setName('rpc:documentation:generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->router->getRouteCollection();

        $builder = new SpecificationBuilder();

        foreach ($routes->getIterator() as $name => $route) {
            foreach ($route->getMethods() as $method) {
                /**
                 * @var Entrypoint $entrypoint
                 */
                $entrypoint = unserialize($route->getDefault('_schema'));

                if (!$entrypoint) {
                    continue;
                }

                $request = null;
                $responses = [];

                if ($entrypoint->input->hasSchema()) {
                    $request = new RequestSchema($entrypoint->input->getContentType(), $entrypoint->input->getSchema());
                }

                if ($entrypoint->output->hasSchema()) {
                    $responses[] = new ResponseSchema(200, $entrypoint->output->getContentType(), $entrypoint->output->getSchema());
                }

                $builder->addPath($method, $route->getPath(), $request, $responses, $entrypoint->tags, $entrypoint->description);
            }
        }

        $specification = $builder->getSpecification();

        $content = json_encode($specification->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents('swagger.json', $content);

        echo $content . PHP_EOL;

        return 0;
    }
}
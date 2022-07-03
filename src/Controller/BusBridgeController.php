<?php


namespace inisire\RPC\Controller;


use inisire\RPC\Bridge\HttpBridge;
use inisire\RPC\Bus\CommandBus;
use inisire\RPC\Error\NotFound;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\Result;
use inisire\RPC\Schema\Entrypoint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BusBridgeController extends AbstractController
{
    public function __construct(
        private HttpBridge $httpBridge,
        private ServiceLocator $entrypoints,
        private ValidatorInterface $validator
    )
    {
    }

    private function executeEntrypoint(Entrypoint $rpc, Request $request): Result
    {
        $mappingErrors = [];
        $command = $this->httpBridge->createCommand($request, $rpc->input, $mappingErrors);

        if (!empty($mappingErrors)) {
            return new ValidationError($mappingErrors);
        }

        if ($command) {
            $violations = $this->validator->validate($command);

            if ($violations->count() > 0) {
                return ValidationError::createByViolations($violations);
            }
        }

        list ($class, $method) = $request->attributes->get('_command_handler');

        if (!$this->entrypoints->has($class)) {
            return new NotFound();
        }

        $service = $this->entrypoints->get($class);

        if ($command) {
            $args = [$command];
        } else {
            $args = [];
        }

        return call_user_func_array([$service, $method], $args);
    }

    public function __invoke(Request $request)
    {
        $rpc = $this->httpBridge->resolveRPC($request);
        $result = $this->executeEntrypoint($rpc, $request);

        return $this->httpBridge->createResponse($result, $rpc->output);
    }
}
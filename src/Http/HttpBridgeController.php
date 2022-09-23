<?php


namespace inisire\RPC\Http;


use inisire\DataObject\DataObjectWizard;
use inisire\RPC\Entrypoint\EntrypointRegistry;
use inisire\RPC\Error\AccessDenied;
use inisire\RPC\Error\NotFound;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Http\Context\RequestContext;
use inisire\RPC\Result\Result;
use inisire\RPC\Result\ResultInterface;
use inisire\RPC\Security\Authorization;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class HttpBridgeController extends AbstractController
{
    public function __construct(
        private HttpBridge         $httpBridge,
        private DataObjectWizard   $wizard,
        private EntrypointRegistry $entrypointRegistry,
    )
    {
    }

    #[Route(
        path: '/{name}',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, string $name)
    {
        $result = $this->execute($name, $request);

        return $this->httpBridge->createResponse($result);
    }

    private function execute(string $name, Request $request): ResultInterface
    {
        $entrypoint = $this->entrypointRegistry->getEntrypoint($name);

        if (!$entrypoint) {
            return new NotFound();
        }

        $parameter = null;
        if ($entrypoint->getInputSchema()) {
            $requestData = $this->httpBridge->extractRequestData($request);

            $errors = [];
            $parameter = $this->wizard->map($entrypoint->getInputSchema(), $requestData, $errors);

            if (!empty($errors)) {
                return new ValidationError($errors);
            }
        }

        $result = $entrypoint->execute($parameter, new RequestContext($request, $this->getUser()));

        if ($result->getOutput() !== null && $entrypoint->getOutputSchema()) {
            $output = $this->wizard->transform($entrypoint->getOutputSchema(), $result->getOutput());
            $result = new Result($output);
        }

        return $result;
    }
}
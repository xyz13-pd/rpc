<?php


namespace inisire\RPC\Controller;


use inisire\RPC\Bridge\HttpBridge;
use inisire\RPC\Bus\CommandBus;
use inisire\RPC\Error\ValidationError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


class BusBridgeController extends AbstractController
{
    private CommandBus $bus;

    private HttpBridge $httpBridge;

    public function __construct(CommandBus $bus, HttpBridge $httpBridge)
    {
        $this->bus = $bus;
        $this->httpBridge = $httpBridge;
    }

    /**
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        $rpc = $this->httpBridge->resolveRPC($request);

        $errors = [];
        $command = $this->httpBridge->createCommand($request, $rpc->input, $errors);

        if (empty($errors)) {
            $result = $this->bus->execute($command);
        } else {
            $result = new ValidationError($errors);
        }
        
        return $this->httpBridge->createResponse($result, $rpc->output);
    }
}
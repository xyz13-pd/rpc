<?php

namespace inisire\RPC\Bridge;

use inisire\DataObject\DataObjectWizard;
use inisire\RPC\Bus\CommandInterface;
use inisire\RPC\Context\RequestContextAwareInterface;
use inisire\RPC\Error\Serializer\ValidationErrorSerializer;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\ErrorResultInterface;
use inisire\RPC\Result\FileStreamResult;
use inisire\RPC\Result\Result;
use inisire\RPC\Result\ResultInterface;
use inisire\RPC\Result\SuccessResultInterface;
use inisire\RPC\Schema\Entrypoint;
use inisire\RPC\Schema\Data;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HttpBridge
{
    public function __construct(
        private DataObjectWizard $wizard
    )
    {
    }

    public function resolveRPC(Request $request): Entrypoint
    {
        return unserialize($request->attributes->get('_schema'));
    }

    public function createCommand(Request $request, Data $input, array &$errors): ?CommandInterface
    {
        if ($request->getMethod() === 'GET') {
            $data = $request->query->all();
        } elseif ($request->getMethod() === 'POST' && $request->getContentType() == 'json') {
            // TODO: Check json errors
            $data = json_decode($request->getContent(), true);
        } elseif ($request->getMethod() === 'POST' && $request->getContentType() == 'form') {
            $data = array_merge($request->request->all(), $request->files->all());
        } elseif ($request->getMethod() === 'POST') {
            $data = $request->request->all();
        } else {
            $data = [];
        }

        $rpc = $this->resolveRPC($request);

        $command = null;
        
        if ($rpc->input->hasSchema()) {
            $command = $this->wizard->map($rpc->input->getSchema(), $data, $errors);
        }

        if ($command && $command instanceof RequestContextAwareInterface) {
            $command->applyContext($request);
        }

        return $command;
    }

    public function createResponse(ResultInterface $result, Data $output): Response
    {
        if ($result instanceof ErrorResultInterface) {
            $response = $this->createErrorResult($result);
        } elseif ($result instanceof SuccessResultInterface) {
            $response = $this->createSuccessResponse($result, $output);
        } else {
            throw new \RuntimeException(sprintf("Unsupported result '%'", $result::class));
        }

        return $response;
    }

    private function createSuccessResponse(SuccessResultInterface $result, Data $output)
    {
        $metadata = $result->getMetadata()->toArray();

        $headers = $metadata['http.headers'] ?? [];
        $statusCode = $metadata['http.statusCode'] ?? Response::HTTP_OK;

        if ($output->hasSchema()) {
            $responseData = $result->getData() !== null
                ? $this->wizard->transform($output->getSchema(), $result->getData())
                : null;
        } else {
            $responseData = $result->getData();
        }

        if ($result instanceof FileStreamResult) {
            $callback = function () use ($result) {
                echo $result->getStream()->getContents();
                flush();
            };
            $response = new StreamedResponse($callback, $statusCode, $headers);
        } elseif ($result instanceof Result) {
            $response = match ($output->getContentType()) {
                'application/json' => new JsonResponse(['data' => $responseData, 'error' => null], $statusCode, $headers),
                default => new Response($responseData, $statusCode, $headers + ['Content-Type' => $output->getContentType()])
            };
        } else {
            throw new \RuntimeException(sprintf("Unsupported result '%s'", $result::class));
        }

        return $response;
    }

    private function createErrorResult(ErrorResultInterface $result)
    {
        if ($result instanceof ValidationError) {
            $serializedError = ValidationErrorSerializer::serialize($result);
        } else {
            $serializedError = [
                'code' => $result->getCode(),
                'message' => $result->getMessage()
            ];
        }

        $metadata = $result->getMetadata()->toArray();
        $headers = $metadata['http.headers'] ?? [];
        $statusCode = $metadata['http.statusCode'] ?? Response::HTTP_BAD_REQUEST;

        return new JsonResponse([
            'data' => null,
            'error' => $serializedError
        ], $statusCode, $headers);
    }
}
<?php

namespace inisire\CQRS\Bridge;

use inisire\CQRS\Annotation\RPC;
use inisire\CQRS\Bus\CommandInterface;
use inisire\CQRS\Context\RequestContextAwareInterface;
use inisire\CQRS\Error\HttpErrorInterface;
use inisire\CQRS\Error\Serializer\ValidationErrorSerializer;
use inisire\CQRS\Error\ValidationError;
use inisire\CQRS\Result\Data\StreamData;
use inisire\CQRS\Result\ErrorResultInterface;
use inisire\CQRS\Result\FileStreamResult;
use inisire\CQRS\Result\HttpAwareResultInterface;
use inisire\CQRS\Result\ResultInterface;
use inisire\CQRS\Result\SuccessResultInterface;
use inisire\CQRS\Schema\FormData;
use inisire\CQRS\Schema\Json;
use inisire\CQRS\Schema\Schema;
use inisire\CQRS\Schema\Stream;
use inisire\DataObject\Serializer\ObjectReferenceSerializer;
use inisire\DataObject\Util\DataMapper;
use inisire\DataObject\Util\DataTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HttpBridge
{
    private DataMapper $mapper;

    private DataTransformer $transformer;

    public function __construct(ObjectReferenceSerializer $objectReferenceSerializer)
    {
        $this->mapper = new DataMapper();
        $this->mapper->registerSerializer($objectReferenceSerializer);

        $this->transformer = new DataTransformer();
        $this->transformer->registerSerializer($objectReferenceSerializer);
    }

    public function resolveRPC(Request $request): RPC
    {
        return unserialize($request->attributes->get('_schema'));
    }

    public function createCommand(Request $request, Schema $input, array &$errors): CommandInterface
    {
        if ($request->getMethod() === 'GET') {
            $data = $request->query->all();
        } elseif ($request->getMethod() === 'POST' && $request->getContentType() == 'json') {
            // TODO: Check json errors
            $data = json_decode($request->getContent(), true);
        } elseif ($request->getMethod() === 'POST' && $request->getContentType() == 'form') {
            $data = $request->request->all();
        } elseif ($request->getMethod() === 'POST') {
            $data = array_merge($request->request->all(), $request->files->all());
        } else {
            $data = [];
        }

        $rpc = $this->resolveRPC($request);

        if ($rpc->input instanceof Json || $rpc->input instanceof FormData) {
            $command = $this->mapper->object($rpc->input->schema, $data, $errors);
        } else {
            throw new \RuntimeException(sprintf('RPC "%s" should contain input schema', $rpc->path));
        }

        if ($command instanceof RequestContextAwareInterface) {
            $command->applyContext($request);
        }

        return $command;
    }

    public function createResponse(ResultInterface $result, Schema $output): Response
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

    private function createSuccessResponse(SuccessResultInterface $result, Schema $output)
    {
        $metadata = $result->getMetadata()->toArray();

        $headers = $metadata['http.headers'] ?? [];
        $statusCode = $metadata['http.statusCode'] ?? Response::HTTP_OK;

        if ($result instanceof FileStreamResult) {
            $callback = function () use ($result) {
                echo $result->getStream()->getContents();
                flush();
            };
            $response = new StreamedResponse($callback, $statusCode, $headers);
        } elseif ($output instanceof Json) {
            $response = new JsonResponse([
                'data'  => $result->getData() !== null
                    ? $this->transformer->any($result->getData(), $output->schema)
                    : null,
                'error' => null
            ], Response::HTTP_OK, $headers);
        } else {
            throw new \RuntimeException(sprintf("Unsupported result '%'", $result::class));
        }

        return $response;
    }

    private function createErrorResult(ErrorResultInterface $result)
    {
        if ($result instanceof ValidationError) {
            $serializedError = ValidationErrorSerializer::serialize($result);
        } else {
            $serializedError = [
                'code'    => $result->getCode(),
                'message' => $result->getMessage()
            ];
        }

        $metadata = $result->getMetadata()->toArray();
        $headers = $metadata['http.headers'] ?? [];
        $statusCode = $metadata['http.statusCode'] ?? Response::HTTP_BAD_REQUEST;

        return new JsonResponse([
            'data'  => null,
            'error' => $serializedError
        ], $statusCode, $headers);
    }
}
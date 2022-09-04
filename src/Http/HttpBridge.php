<?php

namespace inisire\RPC\Http;


use inisire\RPC\Error\ErrorInterface;
use inisire\RPC\Error\Serializer\ValidationErrorSerializer;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\ResultInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


class HttpBridge
{
    public function extractRequestData(Request $request): array
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

        return $data;
    }

    public function createResponse(ResultInterface $result): Response
    {

        $statusCode = Response::HTTP_OK;
        $headers = [];
        $output = $result->getOutput();

        if ($result instanceof HttpResultInterface) {
            $statusCode = $result->getHttpCode();
            $headers = $result->getHttpHeaders();
        }

        if ($result instanceof ErrorInterface) {
            $error = $this->serializeError($result);
            $response = new JsonResponse(['data' => null, 'error' => $error], $statusCode, $headers);
        } elseif ($output instanceof StreamInterface) {
            $callback = function () use ($output) {
                while (!$output->eof()) {
                    $bytes = $output->read(100 * 1024);
                    echo $bytes;
                    flush();
                }
            };
            $response = new StreamedResponse($callback, $statusCode, $headers);
        } elseif (is_array($output) || is_scalar($output)) {
            $response = new JsonResponse(['data' => $output, 'error' => null], $statusCode, $headers);
        } elseif (is_null($output)) {
            $response = new Response(null, $statusCode, $headers);
        } else {
            throw new \RuntimeException(sprintf("Unsupported result output type '%s'", is_object($output) ? $output::class : gettype($output)));
        }

        return $response;
    }


    private function serializeError(ErrorInterface $error)
    {
        if ($error instanceof ValidationError) {
            $serializedError = ValidationErrorSerializer::serialize($error);
        } else {
            $serializedError = [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
            ];
        }

        return $serializedError;
    }
}
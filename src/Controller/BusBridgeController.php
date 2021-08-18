<?php


namespace inisire\CQRS\Controller;


use inisire\CQRS\Annotation\RPC;
use inisire\CQRS\Result\Data\StreamData;
use inisire\CQRS\Result\ErrorResult;
use inisire\CQRS\Schema\FormData;
use inisire\CQRS\Schema\Json;
use inisire\CQRS\Schema\Stream;
use inisire\DataObject\Error\Error;
use inisire\DataObject\Error\ValidationError;
use inisire\DataObject\Serializer\ObjectReferenceSerializer;
use inisire\DataObject\Util\DataMapper;
use inisire\DataObject\Util\DataTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;


class BusBridgeController extends AbstractController
{
    private MessageBusInterface $bus;
    private DataTransformer $transformer;
    private DataMapper $mapper;

    public function __construct(MessageBusInterface $bus, ObjectReferenceSerializer $objectReferenceSerializer)
    {
        $this->bus = $bus;
        $this->mapper = new DataMapper();
        $this->mapper->registerSerializer($objectReferenceSerializer);

        $this->transformer = new DataTransformer();
        $this->transformer->registerSerializer($objectReferenceSerializer);
    }

    /**
     * @param Request $request
     */
    public function __invoke(Request $request)
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

        $errors = [];

        /**
         * @var RPC $schema
         */
        $schema = unserialize($request->attributes->get('_schema'));

        if ($schema->input instanceof Json || $schema->input instanceof FormData) {
            $command = $this->mapper->object($schema->input->schema, $data, $errors);
        } else {
            throw new \RuntimeException(sprintf('RPC "%s" should contain input schema', $schema->path));
        }

        try {
            if (count($errors) === 0) {
                $envelope = $this->bus->dispatch($command);
                $handledStamp = $envelope->last(HandledStamp::class);
                $result = $handledStamp->getResult() ?? new ErrorResult([new Error("The handler hasn't result")]);
            } else {
                $result = new ErrorResult($errors);
            }
        } catch (ValidationFailedException $exception) {
            $result = new ErrorResult(new ValidationError($exception->getViolations()));
        }

        $errors  = [];
        foreach ($result->getErrors() as $error) {
            $errors[] = $error->toArray();
        }
        
        if ($schema->output instanceof Json) {
            $response = new JsonResponse([
                'data' => $result->getData() !== null ? $this->transformer->any($result->getData(), $schema->output->schema) : null,
                'errors' => $errors
            ]); 
        } elseif ($schema->output instanceof Stream) {
            $data = $result->getData();
            if ($data instanceof StreamData) {
                $callback = function () use ($data) {
                    echo $data->getStream()->getContents();
                    flush();
                };
                $response = new StreamedResponse(
                    $callback,
                    200,
                    ['Content-Type' => $data->getMimeType()]
                );
            } else {
                throw new \RuntimeException(sprintf("Invalid stream output data '%'", $data::class));
            }
        } else {
            throw new \RuntimeException(sprintf("Invalid output '%'", $schema->output::class));
        }
        
        return $response; 
    }
}
<?php


namespace inisire\CQRS\Controller;


use inisire\CQRS\Annotation\Command;
use inisire\CQRS\Annotation\Query;
use inisire\CQRS\Result\ErrorResult;
use inisire\DataObject\Error\Error;
use inisire\DataObject\Error\ErrorInterface;
use inisire\DataObject\Error\ValidationError;
use inisire\DataObject\Serializer\ObjectReferenceSerializer;
use inisire\DataObject\Util\DataMapper;
use inisire\DataObject\Util\DataTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            $data = json_decode($request->getContent(), true);
        } elseif ($request->getMethod() === 'POST' && $request->getContentType() == 'form') {
            $data = $request->request->all();
        } else {
            $data = [];
        }

        $errors = [];

        /** @var Command|Query $schema */
        $schema = unserialize($request->attributes->get('_schema'));

        $command = $this->mapper->object($schema->input, $data, $errors);

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

        return new JsonResponse([
            'data' => $result->getData() !== null ? $this->transformer->any($result->getData(), $schema->output) : null,
            'errors' => $errors
        ]);
    }
}
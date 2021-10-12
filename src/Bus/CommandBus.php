<?php


namespace inisire\RPC\Bus;



use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\ResultInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class CommandBus
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function execute(CommandInterface $command): ResultInterface
    {
        try {
            $envelope = $this->bus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            $result = $handledStamp->getResult();
        } catch (ValidationFailedException $exception) {
            $result = ValidationError::createByViolations($exception->getViolations());
        }

        return $result;
    }
}
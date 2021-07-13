<?php


namespace inisire\CQRS\Bus;


use inisire\CQRS\Result\ResultInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CommandBus implements ServiceSubscriberInterface
{
    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public function execute(CommandInterface $command): ResultInterface
    {

    }

    public static function getSubscribedServices()
    {
        return [
            CommandHandlerInterface::class
        ];
    }
}
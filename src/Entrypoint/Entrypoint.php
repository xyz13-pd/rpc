<?php

namespace inisire\RPC\Entrypoint;

use inisire\DataObject\Schema\Type\Type;
use inisire\RPC\Context\CallContext;
use inisire\RPC\Error\AccessDenied;
use inisire\RPC\Error\Unauthorized;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\Result;
use inisire\RPC\Result\ResultInterface;
use inisire\RPC\Security\Authorization;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class Entrypoint
{
    public function __construct(
        private string              $name,
        private ?Type               $input,
        private ?Type               $output,
        private ?string             $description,
        private object              $service,
        private string              $method,
        private ?Authorization      $authorization = null,
        private ?ValidatorInterface $validator = null,
        private ?Security           $security = null
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInputSchema(): ?Type
    {
        return $this->input;
    }

    public function getOutputSchema(): ?Type
    {
        return $this->output;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    /**
     * @return array<\ReflectionParameter>
     */
    private function getParameters(): array
    {
        $reflection = new \ReflectionMethod($this->service, $this->method);

        return $reflection->getParameters();
    }

    private function getCallable(): callable
    {
        return [$this->service, $this->method];
    }

    public function execute(mixed $parameter, ?CallContext $context): ResultInterface
    {
        if ($this->validator && $this->getInputSchema() !== null) {
            $violations = $this->validator->validate($parameter);
            if ($violations->count() > 0) {
                return ValidationError::createByViolations($violations);
            }
        }

        if ($this->getAuthorization()) {
            if (!$this->security) {
                throw new \RuntimeException('Security not exists');
            } elseif ($this->security->getUser() === null) {
                return new Unauthorized();
            } elseif ($this->security->isGranted($this->getAuthorization()->getRole()) === false) {
                return new AccessDenied();
            }
        }

        $arguments = [];
        foreach ([$parameter, $context] as $argument) {
            if ($argument === null) {
                continue;
            }
            $arguments[] = $argument;
        }

        $result = call_user_func_array($this->getCallable(), $arguments);

        if (!$result instanceof ResultInterface) {
            $result = new Result($result);
        }

        return $result;
    }
}
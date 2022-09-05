<?php

namespace inisire\RPC\Entrypoint;

use inisire\DataObject\Schema\Type\Type;
use inisire\RPC\Context\CallContext;
use inisire\RPC\Error\ValidationError;
use inisire\RPC\Result\Result;
use inisire\RPC\Result\ResultInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class Entrypoint
{
    public function __construct(
        private string $name,
        private ?Type $input,
        private ?Type $output,
        private ?string $description,
        private object $service,
        private string $method,
        private ?ValidatorInterface $validator = null
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

        $result = call_user_func_array($this->getCallable(), [$parameter, $context]);

        if (!$result instanceof ResultInterface) {
            $result = new Result($result);
        }

        return $result;
    }
}
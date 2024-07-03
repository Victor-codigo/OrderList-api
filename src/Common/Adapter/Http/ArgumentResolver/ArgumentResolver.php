<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\Exception\ParametersException;
use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private RequestValidation $requestValidation
    ) {
    }

    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (!$this->supports($argument)) {
            return new \ArrayIterator();
        }

        $this->requestValidation->__invoke($request);
        $requestDto = $argument->getType();

        try {
            yield new $requestDto($request);
        } catch (\Exception $e) {
            throw ParametersException::fromMessage('Some of the parameters passed are wrong: '.$e->getMessage());
        }
    }

    private function supports(ArgumentMetadata $argument): bool
    {
        if (null === $argument->getType()) {
            return false;
        }

        $requestReflection = new \ReflectionClass($argument->getType());

        return $requestReflection->implementsInterface(RequestDtoInterface::class);
    }
}

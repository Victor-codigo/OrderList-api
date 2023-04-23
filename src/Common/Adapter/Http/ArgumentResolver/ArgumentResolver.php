<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

class ArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private RequestValidation $requestValidation
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (!$this->supports($argument)) {
            return new \ArrayIterator();
        }

        $this->requestValidation->__invoke($request);
        $requestDto = $argument->getType();

        yield new $requestDto($request);
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

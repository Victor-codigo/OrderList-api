<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\Exception\ParametersException;
use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

class ArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private RequestValidation $requestValidation,
        private bool $appDebug
    ) {
    }

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
    private function supports(ArgumentMetadata $argument): bool
    {
        if (null === $argument->getType()) {
            return false;
        }

        if ($this->supportsForNelmioApiDocBundle($argument)) {
            return false;
        }

        $requestReflection = new \ReflectionClass($argument->getType());

        return $requestReflection->implementsInterface(RequestDtoInterface::class);
    }

    /**
     * Necessary for NelmioApiDocBundle, to process the api documentation.
     */
    private function supportsForNelmioApiDocBundle(ArgumentMetadata $argument): bool
    {
        if (!$this->appDebug) {
            return false;
        }

        if ('area' === $argument->getName() && 'string' === $argument->getType()) {
            return true;
        }

        return false;
    }
}

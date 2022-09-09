<?php

declare(strict_types=1);

namespace Common\Adapter\Http\Request;

use Common\Adapter\Http\Dto\IRequestDto;
use Generator;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestArgumentResolver implements ArgumentValueResolverInterface
{
    private RequestValidation $requestValidation;

    public function __construct(RequestValidation $requestValidation)
    {
        $this->requestValidation = $requestValidation;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $requestReflection = new ReflectionClass($argument->getType());

        return $requestReflection->implementsInterface(IRequestDto::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $this->requestValidation->execute($request);
        $requestDto = $argument->getType();

        yield new $requestDto($request);
    }
}

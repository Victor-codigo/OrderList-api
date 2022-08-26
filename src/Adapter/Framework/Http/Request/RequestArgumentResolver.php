<?php

declare(strict_types=1);

namespace App\Adapter\Framework\Http\Request;

use Adapter\Framework\Http\Request\RequestValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

class RequestArgumentResolver implements ArgumentValueResolverInterface
{
    private RequestValidation $requestValidation;

    public function __construct(RequestValidation $requestValidation)
    {
        $this->requestValidation = $requestValidation;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $this->requestValidation->validateContentType($request);
    }
}

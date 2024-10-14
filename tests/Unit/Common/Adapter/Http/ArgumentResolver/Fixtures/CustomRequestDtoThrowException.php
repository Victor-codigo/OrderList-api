<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomRequestDtoThrowException implements RequestDtoInterface
{
    // @phpstan-ignore constructor.unusedParameter
    public function __construct(Request $request)
    {
        throw new \Exception();
    }
}

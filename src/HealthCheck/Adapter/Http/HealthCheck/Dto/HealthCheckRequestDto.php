<?php

declare(strict_types=1);

namespace HealthCheck\Adapter\Http\HealthCheck\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class HealthCheckRequestDto implements RequestDtoInterface
{
    // @phpstan-ignore constructor.unusedParameter
    public function __construct(Request $request)
    {
    }
}

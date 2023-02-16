<?php

declare(strict_types=1);

namespace Module\Adapter\Http\Controller\Endpoint\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class EndpointRequestDto implements RequestDtoInterface
{
    public function __construct(Request $request)
    {
    }
}

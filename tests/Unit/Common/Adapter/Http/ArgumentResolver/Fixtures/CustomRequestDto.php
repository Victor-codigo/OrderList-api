<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomRequestDto implements RequestDtoInterface
{
    private Request $request;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}

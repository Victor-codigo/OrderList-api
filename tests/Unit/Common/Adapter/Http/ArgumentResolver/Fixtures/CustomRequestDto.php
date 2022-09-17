<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures;

use Common\Adapter\Http\Dto\IRequestDto;
use Symfony\Component\HttpFoundation\Request;

class CustomRequestDto implements IRequestDto
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

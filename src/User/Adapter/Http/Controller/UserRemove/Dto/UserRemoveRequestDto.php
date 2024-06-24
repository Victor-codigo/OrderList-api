<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserRemoveRequestDto implements RequestDtoInterface
{
    public function __construct(Request $request)
    {
    }
}

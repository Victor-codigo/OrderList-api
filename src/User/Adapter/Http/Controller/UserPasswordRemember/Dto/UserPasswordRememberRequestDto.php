<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRemember\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserPasswordRememberRequestDto implements RequestDtoInterface
{
    public readonly string|null $email;

    public function __construct(Request $request)
    {
        $this->email = $request->request->get('email');
    }
}

<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserEmailChange\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserEmailChangeRequestDto implements RequestDtoInterface
{
    public readonly ?string $email;
    public readonly ?string $password;

    public function __construct(Request $request)
    {
        $this->email = $request->request->get('email');
        $this->password = $request->request->get('password');
    }
}

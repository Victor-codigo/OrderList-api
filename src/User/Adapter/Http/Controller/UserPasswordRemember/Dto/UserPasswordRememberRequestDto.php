<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRemember\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserPasswordRememberRequestDto implements RequestDtoInterface
{
    public readonly ?string $email;
    public readonly ?string $passwordRememberUrl;

    public function __construct(Request $request)
    {
        $this->email = $request->request->get('email');
        $this->passwordRememberUrl = $request->request->get('email_password_remember_url');
    }
}

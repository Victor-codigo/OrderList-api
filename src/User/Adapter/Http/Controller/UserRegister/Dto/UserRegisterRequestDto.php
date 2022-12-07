<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRegister\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserRegisterRequestDto implements RequestDtoInterface
{
    public readonly string|null $email;
    public readonly string|null $password;
    public readonly string|null $name;
    public readonly string|null $registrationKey;
    public readonly string|null $userRegisterEmailConfirmationUrl;

    public function __construct(Request $request)
    {
        $this->email = $request->request->get('email');
        $this->password = $request->request->get('password');
        $this->name = $request->request->get('name');
        $this->userRegisterEmailConfirmationUrl = $request->get('email_confirmation_url');
    }
}

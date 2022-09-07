<?php

declare(strict_types=1);

namespace Common\Adapter\Port\Http\Dto;

use Symfony\Component\HttpFoundation\Request;

class UserCreateRequestDto implements IRequestDto
{
    public readonly string|null $email;
    public readonly string|null $password;
    public readonly string|null $name;

    public function __construct(Request $request)
    {
        $this->email = $request->get('email');
        $this->password = $request->get('password');
        $this->name = $request->get('name');
    }
}

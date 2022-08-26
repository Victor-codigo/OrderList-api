<?php

declare(strict_types=1);

namespace Adapter\Domain\User\Request\Dto;

use Adapter\Framework\Http\Dto\IRequestDto;
use Symfony\Component\HttpFoundation\Request;

final class UserCreateInputDto implements IRequestDto
{
    public readonly string|null $email;
    public readonly string|null $password;
    public readonly string|null $name;

    public function __construct(Request $request)
    {
        $this->email = $request->get('name');
        $this->password = $request->get('password');
        $this->name = $request->get('name');
    }
}

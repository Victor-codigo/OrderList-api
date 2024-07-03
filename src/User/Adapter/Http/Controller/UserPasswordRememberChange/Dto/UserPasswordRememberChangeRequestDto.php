<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRememberChange\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserPasswordRememberChangeRequestDto implements RequestDtoInterface
{
    public readonly ?string $token;
    public readonly ?string $passwordNew;
    public readonly ?string $passwordNewRepeat;

    public function __construct(Request $request)
    {
        $this->token = $request->request->get('token');
        $this->passwordNew = $request->request->get('passwordNew');
        $this->passwordNewRepeat = $request->request->get('passwordNewRepeat');
    }
}

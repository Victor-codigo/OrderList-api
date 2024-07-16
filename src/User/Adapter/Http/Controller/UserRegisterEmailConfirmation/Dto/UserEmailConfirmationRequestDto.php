<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRegisterEmailConfirmation\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserEmailConfirmationRequestDto implements RequestDtoInterface
{
    public readonly ?string $token;

    public function __construct(Request $request)
    {
        $this->token = $request->request->get('token');
    }
}

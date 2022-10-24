<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserEmailConfirmation\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserEmailConfirmationRequestDto implements RequestDtoInterface
{
    public readonly string|null $token;

    public function __construct(Request $request)
    {
        $this->token = $request->attributes->get('token');
    }
}

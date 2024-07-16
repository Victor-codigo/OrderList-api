<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordChange\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserPasswordChangeRequestDto implements RequestDtoInterface
{
    public readonly ?string $id;
    public readonly ?string $passwordOld;
    public readonly ?string $passwordNew;
    public readonly ?string $passwordNewRepeat;

    public function __construct(Request $request)
    {
        $this->id = $request->request->get('id');
        $this->passwordOld = $request->request->get('passwordOld');
        $this->passwordNew = $request->request->get('passwordNew');
        $this->passwordNewRepeat = $request->request->get('passwordNewRepeat');
    }
}

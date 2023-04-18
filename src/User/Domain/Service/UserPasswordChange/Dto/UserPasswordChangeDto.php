<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordChange\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Password;

class UserPasswordChangeDto
{
    public readonly Identifier $id;
    public readonly Password $passwordOld;
    public readonly Password $passwordNew;
    public readonly Password $passwordNewRepeat;
    public readonly bool $checkOldPassword;

    public function __construct(Identifier $id, Password $passwordOld, Password $passwordNew, Password $passwordNewRepeat, bool $checkOldPassword)
    {
        $this->id = $id;
        $this->passwordOld = $passwordOld;
        $this->passwordNew = $passwordNew;
        $this->passwordNewRepeat = $passwordNewRepeat;
        $this->checkOldPassword = $checkOldPassword;
    }
}

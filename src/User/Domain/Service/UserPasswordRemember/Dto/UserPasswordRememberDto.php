<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;

class UserPasswordRememberDto
{
    public readonly Email $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }
}

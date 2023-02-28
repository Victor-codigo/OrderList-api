<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Url;

class UserPasswordRememberDto
{
    public readonly Email $email;
    public readonly Url $passwordRememberUrl;

    public function __construct(Email $email, Url $passwordRememberUrl)
    {
        $this->email = $email;
        $this->passwordRememberUrl = $passwordRememberUrl;
    }
}

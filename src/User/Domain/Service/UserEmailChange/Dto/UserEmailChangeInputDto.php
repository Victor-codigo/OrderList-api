<?php

declare(strict_types=1);

namespace User\Domain\Service\UserEmailChange\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Password;

class UserEmailChangeInputDto
{
    public function __construct(
        public readonly Email $userEmail,
        public readonly Email $email,
        public readonly Password $password,
    ) {
    }
}

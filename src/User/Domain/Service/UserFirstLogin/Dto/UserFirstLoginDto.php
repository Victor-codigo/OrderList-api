<?php

declare(strict_types=1);

namespace User\Domain\Service\UserFirstLogin\Dto;

use User\Domain\Model\User;

class UserFirstLoginDto
{
    public function __construct(
        public readonly User $user
    ) {
    }
}

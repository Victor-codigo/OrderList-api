<?php

declare(strict_types=1);

namespace User\Domain\Service\UserCreateGroup\Dto;

use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class UserCreateGroupDto
{
    public function __construct(
        public readonly NameWithSpaces $userName,
    ) {
    }
}

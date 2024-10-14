<?php

declare(strict_types=1);

namespace User\Application\GetUsers\Dto;

use User\Domain\Model\User;

class GetUsersOutputDto
{
    /**
     * @param array<int, User[]> $users
     */
    public function __construct(
        public readonly array $users,
    ) {
    }
}

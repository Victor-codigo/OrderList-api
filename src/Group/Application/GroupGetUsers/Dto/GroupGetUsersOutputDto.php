<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Dto;

class GroupGetUsersOutputDto
{
    public function __construct(
        public readonly array $users
    ) {
    }
}

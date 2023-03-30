<?php

declare(strict_types=1);

namespace User\Application\UserGetByName\Dto;

class UserGetByNameOutputDto
{
    public function __construct(
        public readonly array $userData
    ) {
    }
}

<?php

declare(strict_types=1);

namespace User\Application\UserGetByName\Dto;

class UserGetByNameOutputDto
{
    /**
     * @param array<int, array<string, mixed>> $userData
     */
    public function __construct(
        public readonly array $userData,
    ) {
    }
}

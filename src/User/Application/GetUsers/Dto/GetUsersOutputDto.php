<?php

declare(strict_types=1);

namespace User\Application\GetUsers\Dto;

class GetUsersOutputDto
{
    public readonly array $users;

    public function __construct(array $users)
    {
        $this->users = $users;
    }
}

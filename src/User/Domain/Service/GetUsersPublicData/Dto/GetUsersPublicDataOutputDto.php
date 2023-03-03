<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

class GetUsersPublicDataOutputDto
{
    public readonly array $usersData;

    public function __construct(array $usersData)
    {
        $this->usersData = $usersData;
    }
}

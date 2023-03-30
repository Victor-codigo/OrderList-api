<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

class GetUsersPublicDataOutputDto
{
    public function __construct(
        public readonly array $usersData
    ) {
    }
}

<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData\Dto;

class GetUsersProfilePublicDataOutputDto
{
    public readonly array $profileData;

    public function __construct(array $profileData)
    {
        $this->profileData = $profileData;
    }
}

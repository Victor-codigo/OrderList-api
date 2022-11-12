<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GetUsersProfilePublicDataDto
{
    /** @var Identifier[] */
    public readonly array $usersId;

    public function __construct(array $usersId)
    {
        $this->usersId = $usersId;
    }
}

<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GetUsersPublicDataDto
{
    /**
     * @var Identifier[]
     */
    public readonly array $usersId;

    public function __construct(array $usersId)
    {
        $this->usersId = $usersId;
    }
}

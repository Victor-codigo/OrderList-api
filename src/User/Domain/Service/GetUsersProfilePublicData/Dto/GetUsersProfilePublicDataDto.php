<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GetUsersProfilePublicDataDto
{
    /**
     * @param Identifier[] $usersId
     */
    public function __construct(
        public readonly array $usersId,
    ) {
    }
}

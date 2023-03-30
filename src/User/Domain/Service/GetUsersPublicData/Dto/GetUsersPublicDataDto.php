<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;

class GetUsersPublicDataDto
{
    /**
     * @param Identifier[]|Name[] $users
     */
    public function __construct(
        public readonly array $users
    ) {
    }
}

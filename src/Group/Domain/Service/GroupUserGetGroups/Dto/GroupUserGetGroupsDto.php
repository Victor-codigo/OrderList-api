<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserGetGroupsDto
{
    public function __construct(
        public readonly Identifier $userId
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemoveAllUserGroups\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveAllUserGroupsDto
{
    public function __construct(
        public readonly Identifier $userId,
    ) {
    }
}

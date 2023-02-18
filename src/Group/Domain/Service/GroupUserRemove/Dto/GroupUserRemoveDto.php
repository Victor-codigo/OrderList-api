<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserRemoveDto
{
    /**
     * @param Identifier[] $usersId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $usersId
    ) {
    }
}

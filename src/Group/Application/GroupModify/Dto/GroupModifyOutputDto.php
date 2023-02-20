<?php

declare(strict_types=1);

namespace Group\Application\GroupModify\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupModifyOutputDto
{
    public function __construct(
        public readonly Identifier $groupId
    ) {
    }
}

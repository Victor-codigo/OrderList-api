<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveDto
{
    public function __construct(
        public readonly Identifier $groupId,
    ) {
    }
}

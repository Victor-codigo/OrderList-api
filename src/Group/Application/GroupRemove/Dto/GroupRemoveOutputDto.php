<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveOutputDto
{
    public function __construct(
        public readonly Identifier $groupRemovedId
    ) {
    }
}

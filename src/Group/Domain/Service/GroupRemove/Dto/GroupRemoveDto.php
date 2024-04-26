<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveDto
{
    /**
     * @param Identifier[] $groupsId
     */
    public function __construct(
        public readonly array $groupsId,
    ) {
    }
}

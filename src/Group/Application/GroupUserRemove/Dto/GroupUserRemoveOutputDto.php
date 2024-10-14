<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserRemoveOutputDto
{
    /**
     * @param Identifier[] $usersId
     */
    public function __construct(
        public array $usersId,
    ) {
    }
}

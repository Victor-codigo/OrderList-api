<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRoleChange\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserRoleChangeOutputDto
{
    /**
     * @var Identifier
     */
    public function __construct(
        public readonly array $usersModifiedIds
    ) {
    }
}

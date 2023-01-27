<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserAdd\Dto;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserAddDto
{
    public function __construct(
        public readonly Identifier $groupId,

        /**
         * @var Identifier[] $users
         */
        public readonly array $usersId,
        public readonly Rol $rol,
    ) {
    }
}

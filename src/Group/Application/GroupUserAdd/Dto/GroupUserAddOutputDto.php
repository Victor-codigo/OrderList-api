<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Dto;

class GroupUserAddOutputDto
{
    public function __construct(
        /**
         * @var Identifier[]
         */
        public readonly array $usersId
    ) {
    }
}

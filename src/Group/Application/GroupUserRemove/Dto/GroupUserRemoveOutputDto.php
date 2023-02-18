<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove\Dto;

class GroupUserRemoveOutputDto
{
    public function __construct(
        /**
         * @var Identifier[]
         */
        public array $usersId
    ) {
    }
}

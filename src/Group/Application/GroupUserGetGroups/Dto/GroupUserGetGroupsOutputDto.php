<?php

declare(strict_types=1);

namespace Group\Application\GroupUserGetGroups\Dto;

class GroupUserGetGroupsOutputDto
{
    public function __construct(
        public readonly \Generator $groups
    ) {
    }
}

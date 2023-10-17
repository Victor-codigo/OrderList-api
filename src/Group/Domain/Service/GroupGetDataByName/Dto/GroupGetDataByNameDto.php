<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetDataByName\Dto;

use Common\Domain\Model\ValueObject\String\Name;

class GroupGetDataByNameDto
{
    public function __construct(
        public readonly Name $groupName
    ) {
    }
}

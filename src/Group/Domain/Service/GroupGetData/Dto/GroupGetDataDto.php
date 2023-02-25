<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetData\Dto;

use Group\Domain\Model\GROUP_TYPE;

class GroupGetDataDto
{
    public function __construct(
        public readonly array $groupsId,
        public readonly GROUP_TYPE|null $groupType = null
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetData\Dto;

use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Validation\Group\GROUP_TYPE;

class GroupGetDataDto
{
    public function __construct(
        public readonly array $groupsId,
        public readonly ?GROUP_TYPE $groupType,
        public readonly Path $userImage
    ) {
    }
}

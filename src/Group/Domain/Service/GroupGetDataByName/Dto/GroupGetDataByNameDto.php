<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetDataByName\Dto;

use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;

class GroupGetDataByNameDto
{
    public function __construct(
        public readonly NameWithSpaces $groupName,
        public readonly Path $userImage
    ) {
    }
}

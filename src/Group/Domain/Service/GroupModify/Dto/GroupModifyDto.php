<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify\Dto;

use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;

class GroupModifyDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly Name $name,
        public readonly Description $description,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupCreate\Dto;

use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;

class GroupCreateDto
{
    public function __construct(
        public readonly Identifier $userCreatorId,
        public readonly Name $name,
        public readonly Description $description,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupCreate\Dto;

use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class GroupCreateDto
{
    public function __construct(
        public readonly Identifier $userCreatorId,
        public readonly NameWithSpaces $name,
        public readonly Description $description,
        public readonly GroupType $type,
        public readonly GroupImage $image,
    ) {
    }
}

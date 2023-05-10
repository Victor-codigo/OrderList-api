<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopCreate\Dto;

use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ShopCreateDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly NameWithSpaces $name,
        public readonly Description $description,
        public readonly ShopImage $image
    ) {
    }
}

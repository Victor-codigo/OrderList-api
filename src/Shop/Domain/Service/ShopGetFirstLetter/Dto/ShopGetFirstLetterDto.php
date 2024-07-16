<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetFirstLetter\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ShopGetFirstLetterDto
{
    public function __construct(
        public Identifier $groupId
    ) {
    }
}

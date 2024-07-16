<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetFirstLetter\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ProductGetFirstLetterDto
{
    public function __construct(
        public Identifier $groupId
    ) {
    }
}

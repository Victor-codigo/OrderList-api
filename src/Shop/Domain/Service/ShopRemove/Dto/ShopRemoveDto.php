<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ShopRemoveDto
{
    /**
     * @param array<int, Identifier> $shopsId
     */
    public function __construct(
        public readonly array $shopsId,
        public readonly Identifier $groupId,
    ) {
    }
}

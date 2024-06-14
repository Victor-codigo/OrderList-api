<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopRemoveAllGroupsShops\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ShopRemoveAllGroupsShopsDto
{
    /**
     * @param Identifier[] $groupsId
     */
    public function __construct(
        public readonly array $groupsId,
    ) {
    }
}

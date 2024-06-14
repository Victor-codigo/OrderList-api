<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemoveAllGroupsProducts\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveAllGroupsProductsDto
{
    /**
     * @param Identifier[] $groupsId
     */
    public function __construct(
        public readonly array $groupsId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ShopRemoveDto
{
    public function __construct(
        public readonly Identifier $shopId,
        public readonly Identifier $groupId,
        public readonly Identifier $productId
    ) {
    }
}

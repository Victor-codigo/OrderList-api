<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveDto
{
    public function __construct(
        public readonly Identifier $productId,
        public readonly Identifier $groupId,
        public readonly Identifier $shopId
    ) {
    }
}

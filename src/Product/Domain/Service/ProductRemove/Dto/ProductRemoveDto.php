<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveDto
{
    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $productsId,
        public readonly array $shopsId
    ) {
    }
}

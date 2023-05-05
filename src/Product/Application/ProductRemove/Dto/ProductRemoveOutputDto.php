<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $productId
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->productId->getValue(),
        ];
    }
}

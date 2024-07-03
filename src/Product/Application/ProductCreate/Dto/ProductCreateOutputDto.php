<?php

declare(strict_types=1);

namespace Product\Application\ProductCreate\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductCreateOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $productId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->productId->getValue(),
        ];
    }
}

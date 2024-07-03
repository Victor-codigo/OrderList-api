<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $productsId
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'id' => array_map(
                fn (Identifier $productId) => $productId->getValue(),
                $this->productsId
            ),
        ];
    }
}

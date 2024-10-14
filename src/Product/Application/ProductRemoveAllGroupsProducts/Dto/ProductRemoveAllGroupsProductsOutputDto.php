<?php

declare(strict_types=1);

namespace Product\Application\ProductRemoveAllGroupsProducts\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductRemoveAllGroupsProductsOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $productsId
     */
    public function __construct(
        public readonly array $productsId,
    ) {
    }

    /**
     * @return array{ id: string[] }
     */
    #[\Override]
    public function toArray(): array
    {
        $productsIds = array_map(
            fn (Identifier $productId): ?string => $productId->getValue(),
            $this->productsId
        );

        return [
            'id' => $productsIds,
        ];
    }
}

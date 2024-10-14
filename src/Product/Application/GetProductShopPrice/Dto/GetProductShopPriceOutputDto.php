<?php

declare(strict_types=1);

namespace Product\Application\GetProductShopPrice\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class GetProductShopPriceOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  product_id: string|null,
     *  shop_id: string|null,
     *  price: float|null,
     *  unit: string|null
     * }> $productsShops
     */
    public function __construct(
        private array $productsShops,
    ) {
    }

    /**
     * @return array<int, array{
     *  product_id: string|null,
     *  shop_id: string|null,
     *  price: float|null,
     *  unit: string|null
     * }>
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->productsShops;
    }
}

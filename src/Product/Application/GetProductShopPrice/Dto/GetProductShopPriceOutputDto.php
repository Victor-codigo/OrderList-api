<?php

declare(strict_types=1);

namespace Product\Application\GetProductShopPrice\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;

class GetProductShopPriceOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private array $productsShops
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return $this->productsShops;
    }
}

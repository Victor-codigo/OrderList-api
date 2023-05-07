<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ProductGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $productsData
    ) {
    }

    public function toArray(): array
    {
        return $this->productsData;
    }
}

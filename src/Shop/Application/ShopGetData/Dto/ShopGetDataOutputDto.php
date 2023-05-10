<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ShopGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $shopsData
    ) {
    }

    public function toArray(): array
    {
        return $this->shopsData;
    }
}

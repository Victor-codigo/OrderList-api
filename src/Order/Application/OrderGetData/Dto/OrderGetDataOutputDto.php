<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class OrderGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $ordersData
    ) {
    }

    public function toArray(): array
    {
        return $this->ordersData;
    }
}

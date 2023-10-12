<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetOrders\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ListOrdersGetOrdersOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $listOrderOrdersData
    ) {
    }

    public function toArray(): array
    {
        return $this->listOrderOrdersData;
    }
}

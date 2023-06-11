<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ListOrdersGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private array $listsOrdersData
    ) {
    }

    public function toArray(): array
    {
        return $this->listsOrdersData;
    }
}

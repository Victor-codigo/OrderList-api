<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use ListOrders\Domain\Model\ListOrders;

class ListOrdersRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @var ListOrders[]
     */
    public function __construct(
        public readonly array $listsOrders
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'id' => array_map(
                fn (ListOrders $listOrderId) => $listOrderId->getId()->getValue(),
                $this->listsOrders
            ),
        ];
    }
}

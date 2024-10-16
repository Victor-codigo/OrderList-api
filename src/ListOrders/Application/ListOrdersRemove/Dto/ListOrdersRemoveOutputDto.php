<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use ListOrders\Domain\Model\ListOrders;

class ListOrdersRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @param ListOrders[] $listsOrders
     */
    public function __construct(
        public readonly array $listsOrders,
    ) {
    }

    /**
     * @return array{ id: string[] }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => array_map(
                fn (ListOrders $listOrderId): ?string => $listOrderId->getId()->getValue(),
                $this->listsOrders
            ),
        ];
    }
}

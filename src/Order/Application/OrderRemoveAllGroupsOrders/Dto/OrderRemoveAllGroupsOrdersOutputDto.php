<?php

declare(strict_types=1);

namespace Order\Application\OrderRemoveAllGroupsOrders\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class OrderRemoveAllGroupsOrdersOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $ordersIdRemoved
     * @param Identifier[] $ordersIdUserIdChanged
     */
    public function __construct(
        public readonly array $ordersIdRemoved,
        public readonly array $ordersIdUserIdChanged,
    ) {
    }

    /**
     * @return array{
     *  orders_id_removed: string[],
     *  orders_id_user_changed: string[]
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        $ordersIdRemoved = array_map(
            fn (Identifier $orderId): ?string => $orderId->getValue(),
            $this->ordersIdRemoved
        );
        $ordersIdUserChanged = array_map(
            fn (Identifier $orderId): ?string => $orderId->getValue(),
            $this->ordersIdUserIdChanged
        );

        return [
            'orders_id_removed' => $ordersIdRemoved,
            'orders_id_user_changed' => $ordersIdUserChanged,
        ];
    }
}

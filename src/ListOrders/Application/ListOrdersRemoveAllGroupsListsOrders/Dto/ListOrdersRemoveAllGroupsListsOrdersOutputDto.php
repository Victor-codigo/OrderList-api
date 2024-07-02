<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveAllGroupsListsOrdersOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $listsOrdersIdRemoved
     * @param Identifier[] $listsOrdersIdUserIdChanged
     */
    public function __construct(
        public readonly array $listsOrdersIdRemoved,
        public readonly array $listsOrdersIdUserIdChanged
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        $listsOrdersIdRemoved = array_map(
            fn (Identifier $listOrdersId) => $listOrdersId->getValue(),
            $this->listsOrdersIdRemoved
        );
        $listsOrdersIdUserChanged = array_map(
            fn (Identifier $listOrdersId) => $listOrdersId->getValue(),
            $this->listsOrdersIdUserIdChanged
        );

        return [
            'lists_orders_id_removed' => $listsOrdersIdRemoved,
            'lists_orders_id_user_changed' => $listsOrdersIdUserChanged,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderRemoveAllGroupsOrdersDto
{
    /**
     * @param Identifier[] $groupsIdToRemoveOrders
     * @param Identifier[] $groupsIdToChangeOrdersUser
     */
    public function __construct(
        public readonly array $groupsIdToRemoveOrders,
        public readonly array $groupsIdToChangeOrdersUser,
        public readonly ?Identifier $userIdToSet,
    ) {
    }
}

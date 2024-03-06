<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveDto
{
    /**
     * @param Identifier[] $listsOrdersId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $listsOrdersId,
    ) {
    }
}

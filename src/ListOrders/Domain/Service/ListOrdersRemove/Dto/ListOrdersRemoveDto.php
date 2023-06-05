<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveDto
{
    public function __construct(
        public readonly Identifier $listOrdersId,
        public readonly Identifier $groupId
    ) {
    }
}

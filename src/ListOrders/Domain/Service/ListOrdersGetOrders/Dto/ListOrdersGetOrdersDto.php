<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetOrders\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersGetOrdersDto
{
    public function __construct(
        public readonly Identifier $listOrderId,
        public readonly Identifier $groupId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems
    ) {
    }
}

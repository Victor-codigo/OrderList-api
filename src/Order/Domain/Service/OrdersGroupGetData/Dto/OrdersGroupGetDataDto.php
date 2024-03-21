<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrdersGroupGetData\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;

class OrdersGroupGetDataDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly bool $orderAsc
    ) {
    }
}

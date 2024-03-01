<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersGetDataDto
{
    /**
     * @param Identifier[] $listOrdersId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $listOrdersId,
        public readonly bool $orderAsc,

        public readonly Filter|null $filterSection,
        public readonly Filter|null $filterText,

        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
    ) {
    }
}

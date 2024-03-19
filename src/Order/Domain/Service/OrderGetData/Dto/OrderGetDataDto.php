<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;

class OrderGetDataDto
{
    /**
     * @param Identifier[] $ordersId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly IdentifierNullable $listOrdersId,
        public readonly array $ordersId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly bool $orderAsc,
        public readonly ?Filter $filterSection,
        public readonly ?Filter $filterText
    ) {
    }
}

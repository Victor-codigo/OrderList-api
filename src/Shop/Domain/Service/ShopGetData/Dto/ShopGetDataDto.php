<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ShopGetDataDto
{
    /**
     * @param Identifier[] $productsId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $shopsId,
        public readonly array $productsId,
        public readonly Filter $shopFilter,
        public readonly NameWithSpaces $shopName,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly bool $orderAsc,
    ) {
    }
}

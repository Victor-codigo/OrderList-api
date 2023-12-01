<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ProductGetDataDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $productsId,
        public readonly array $shopsId,
        public readonly NameWithSpaces $productName,

        public readonly Filter $productNameFilter,
        public readonly Filter $shopNameFilter,

        public readonly bool $orderAsc,

        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
    ) {
    }
}

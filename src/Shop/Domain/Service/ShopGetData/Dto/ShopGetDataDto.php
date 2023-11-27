<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetData\Dto;

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
        public readonly string|null $shopNameStartsWith,
        public readonly NameWithSpaces $shopName,
        public readonly int $shopsMaxNumber = 100,
        public readonly bool $orderAsc = true
    ) {
    }
}

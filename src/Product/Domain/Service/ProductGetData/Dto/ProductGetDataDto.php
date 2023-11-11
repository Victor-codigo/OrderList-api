<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ProductGetDataDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $productsId,
        public readonly array $shopsId,
        public readonly string|null $productNameStartsWith,
        public readonly NameWithSpaces $productName,
        public readonly int $productsMaxNumber = 100
    ) {
    }
}

<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ListOrdersGetDataDto
{
    /**
     * @param Identifier[] $listOrdersId
     */
    public function __construct(
        public readonly array $listOrdersId,
        public readonly Identifier $groupId,
        public readonly NameWithSpaces $listOrdersNameStartsWith
    ) {
    }
}

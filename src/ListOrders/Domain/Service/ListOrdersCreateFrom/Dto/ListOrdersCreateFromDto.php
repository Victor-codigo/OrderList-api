<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreateFrom\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ListOrdersCreateFromDto
{
    public function __construct(
        public readonly Identifier $listOrdersIdCreateFrom,
        public readonly Identifier $groupId,
        public readonly Identifier $userId,
        public readonly NameWithSpaces $name,
    ) {
    }
}

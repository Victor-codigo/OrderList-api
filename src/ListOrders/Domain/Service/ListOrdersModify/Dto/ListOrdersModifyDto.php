<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersModify\Dto;

use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ListOrdersModifyDto
{
    public function __construct(
        public readonly Identifier $userId,
        public readonly Identifier $groupId,
        public readonly Identifier $listOrdersId,
        public readonly NameWithSpaces $name,
        public readonly Description $description,
        public readonly DateNowToFuture $dateToBuy
    ) {
    }
}

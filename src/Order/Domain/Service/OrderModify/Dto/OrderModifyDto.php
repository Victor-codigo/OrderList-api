<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderModify\Dto;

use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;

class OrderModifyDto
{
    public function __construct(
        public readonly Identifier $orderId,
        public readonly Identifier $groupId,
        public readonly Identifier $productId,
        public readonly IdentifierNullable $shopId,
        public readonly Identifier $userId,
        public readonly Description $description,
        public readonly Amount $amount,
        public readonly UnitMeasure $unit,
    ) {
    }
}

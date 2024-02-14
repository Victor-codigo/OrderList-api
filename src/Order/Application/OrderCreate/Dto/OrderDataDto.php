<?php

declare(strict_types=1);

namespace Order\Application\OrderCreate\Dto;

use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;

class OrderDataDto
{
    public function __construct(
        public readonly Identifier $productId,
        public readonly IdentifierNullable $shopId,
        public readonly Identifier $userId,
        public readonly Description $description,
        public readonly Amount $amount,
    ) {
    }
}

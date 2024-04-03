<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderBought\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderBoughtDto
{
    public function __construct(
        public readonly Identifier $orderId,
        public readonly Identifier $groupId,
        public readonly bool $bought,
    ) {
    }
}

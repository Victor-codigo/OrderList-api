<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderGetDataDto
{
    /**
     * @param Identifier[] $ordersId
     */
    public function __construct(
        public readonly array $ordersId,
        public readonly Identifier $groupId
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderCreateDto
{
    /**
     * @param OrderDataServiceDto[] $orders
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $orders
    ) {
    }
}

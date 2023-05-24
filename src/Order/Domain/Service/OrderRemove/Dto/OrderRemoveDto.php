<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderRemoveDto
{
    /**
     * @param Identifier[] $ordersId
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $ordersId
    ) {
    }
}

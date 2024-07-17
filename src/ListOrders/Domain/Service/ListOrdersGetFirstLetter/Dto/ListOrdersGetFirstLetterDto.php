<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetFirstLetter\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ListOrdersGetFirstLetterDto
{
    public function __construct(
        public Identifier $groupId
    ) {
    }
}

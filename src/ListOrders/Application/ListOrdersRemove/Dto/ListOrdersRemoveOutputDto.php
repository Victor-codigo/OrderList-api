<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $listOrdersId
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->listOrdersId->getValue(),
        ];
    }
}

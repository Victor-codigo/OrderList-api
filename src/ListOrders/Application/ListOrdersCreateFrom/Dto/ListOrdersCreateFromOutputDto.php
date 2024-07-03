<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersCreateFrom\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersCreateFromOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $listOrdersId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->listOrdersId->getValue(),
        ];
    }
}

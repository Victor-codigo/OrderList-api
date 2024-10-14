<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersModify\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersModifyOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private Identifier $listOrdersId,
    ) {
    }

    /**
     * @return array{ id: string|null }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->listOrdersId->getValue(),
        ];
    }
}

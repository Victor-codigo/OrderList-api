<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetFirstLetter\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

readonly class ListOrdersGetFirstLetterOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, string> $listOrdersFirstLetter
     */
    public function __construct(
        public array $listOrdersFirstLetter
    ) {
    }

    public function toArray(): array
    {
        return $this->listOrdersFirstLetter;
    }
}

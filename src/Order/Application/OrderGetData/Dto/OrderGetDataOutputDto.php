<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class OrderGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $ordersData,
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'orders' => $this->ordersData,
        ];
    }
}

<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ListOrdersGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private array $listOrdersData,
        private PaginatorPage $page,
        private int $pagesTotal
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'list_orders' => $this->listOrdersData,
        ];
    }
}

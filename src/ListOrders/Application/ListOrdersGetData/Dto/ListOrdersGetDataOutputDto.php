<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ListOrdersGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }> $listOrdersData
     */
    public function __construct(
        private array $listOrdersData,
        private PaginatorPage $page,
        private int $pagesTotal,
    ) {
    }

    /**
     * @return array{
     *  page: int|null,
     *  pages_total: int,
     *  list_orders: array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }>}
     */
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

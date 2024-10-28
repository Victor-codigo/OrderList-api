<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class OrderGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  id: string|null,
     *  group_id: string|null,
     *  list_orders_id: string|null,
     *  user_id: string|null,
     *  description: string|null,
     *  amount: float|null,
     *  bought: bool,
     *  created_on: string,
     *  product: array{
     *      id: string|null,
     *      name: string|null,
     *      address: string|null,
     *      description: string|null,
     *      image: string|null,
     *      created_on: string
     *  },
     *  shop: array{}|array{
     *      id: string|null,
     *      name: string|null,
     *      description: string|null,
     *      created_on: string
     *  },
     *  productShop: array{}|array{
     *      price: float|null,
     *      unit: object|null
     * }}> $ordersData
     */
    public function __construct(
        public readonly array $ordersData,
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
    ) {
    }

    /**
     * @return array{
     *  page: int,
     *  pages_total: int,
     *  orders: array<int, array{
     *  id: string|null,
     *  group_id: string|null,
     *  list_orders_id: string|null,
     *  user_id: string|null,
     *  description: string|null,
     *  amount: float|null,
     *  bought: bool,
     *  created_on: string,
     *  product: array{
     *      id: string|null,
     *      name: string|null,
     *      address: string|null,
     *      description: string|null,
     *      image: string|null,
     *      created_on: string
     *  },
     *  shop: array{}|array{
     *      id: string|null,
     *      name: string|null,
     *      description: string|null,
     *      created_on: string
     *  },
     *  productShop: array{}|array{
     *      price: float|null,
     *      unit: object|null
     * }}>}
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'orders' => $this->ordersData,
        ];
    }
}

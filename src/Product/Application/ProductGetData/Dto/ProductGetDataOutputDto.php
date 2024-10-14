<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ProductGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  image: string|null,
     *  created_on: string
     * }> $productsData
     */
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $productsData,
    ) {
    }

    /**
     * @return array{
     *  page: int|null,
     *  pages_total: int,
     *  products: array<int, array{
     *    id: string,
     *    group_id: string,
     *    name: string,
     *    description: string,
     *    image: string|null,
     *    created_on: string
     * }>
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'products' => $this->productsData,
        ];
    }
}

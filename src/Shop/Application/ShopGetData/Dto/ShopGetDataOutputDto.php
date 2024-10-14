<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ShopGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  id: string,
     *  group_id: string,
     *  name: string,
     *  address: string,
     *  description: string,
     *  image: string|null,
     *  created_on: string
     * }> $shopsData
     */
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $shopsData,
    ) {
    }

    /**
     * @return array{
     *  page: string|null,
     *  pages_total: int,
     *  shops: array<int, array{
     *  id: string,
     *  group_id: string,
     *  name: string,
     *  address: string,
     *  description: string,
     *  image: string|null,
     *  created_on: string
     * }>
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'shops' => $this->shopsData,
        ];
    }
}

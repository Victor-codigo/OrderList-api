<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ProductGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $productsData
    ) {
    }

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

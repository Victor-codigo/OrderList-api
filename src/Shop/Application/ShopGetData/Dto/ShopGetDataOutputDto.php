<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class ShopGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $shopsData,
    ) {
    }

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

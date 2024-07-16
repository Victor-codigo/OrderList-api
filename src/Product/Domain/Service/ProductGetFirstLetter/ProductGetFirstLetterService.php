<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetFirstLetter\Dto\ProductGetFirstLetterDto;

class ProductGetFirstLetterService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * @return array<int, string>
     *
     * @throws DBNotFoundException
     */
    public function __invoke(ProductGetFirstLetterDto $input): array
    {
        return $this->productRepository->findGroupProductsFirstLetterOrFail($input->groupId);
    }
}

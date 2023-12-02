<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductRemove\Dto\ProductRemoveDto;

class ProductRemoveService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private string $productImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     * @throws DBConnectionException
     */
    public function __invoke(ProductRemoveDto $input): Identifier
    {
        $productsToRemove = $this->productRepository->findProductsOrFail($input->groupId, [$input->productId], [$input->shopId]);
        /** @var Product $productToRemove */
        $productToRemove = iterator_to_array($productsToRemove)[0];
        $this->removeImage($productToRemove->getImage());

        $this->productRepository->remove([$productToRemove]);

        return $productToRemove->getId();
    }

    /**
     * @throws DomainInternalErrorException
     */
    private function removeImage(Path $image): void
    {
        $imagePath = $image->getValue();

        if (null === $imagePath) {
            return;
        }

        $imagePath = $this->productImagePath."/{$imagePath}";

        if (!file_exists($imagePath)) {
            return;
        }

        if (!unlink($imagePath)) {
            throw DomainInternalErrorException::fromMessage('The image cannot be deleted');
        }
    }
}

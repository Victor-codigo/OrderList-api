<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductRemove\Dto\ProductRemoveDto;

class ProductRemoveService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EntityImageRemoveService $entityImageRemoveService,
        private string $productImagePath,
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     * @throws DBConnectionException
     */
    public function __invoke(ProductRemoveDto $input): array
    {
        $productsPaginator = $this->productRepository->findProductsOrFail($input->groupId, $input->productsId, $input->shopsId);
        /** @var Product[] $productsToRemove */
        $productsToRemove = iterator_to_array($productsPaginator);

        $this->removeImages($productsToRemove);
        $this->productRepository->remove($productsToRemove);

        return array_map(
            fn (Product $product): Identifier => $product->getId(),
            $productsToRemove
        );
    }

    /**
     * @param Product[] $productsToRemove
     */
    private function removeImages(array $productsToRemove): void
    {
        array_map(
            fn (Product $product) => $this->entityImageRemoveService->__invoke($product, ValueObjectFactory::createPath($this->productImagePath)),
            $productsToRemove
        );
    }
}

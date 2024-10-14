<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetData\Dto\ProductGetDataDto;

class ProductGetDataService
{
    /**
     * @var PaginatorInterface<int, Product>|null
     */
    private ?PaginatorInterface $productsPaginator = null;

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private string $productPublicImagePath,
        private string $appProtocolAndDomain,
    ) {
    }

    /**
     * @return array<int, array{
     *  id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  image: string|null,
     *  created_on:string
     * }>
     *
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(ProductGetDataDto $input): array
    {
        if ($input->groupId->isNull()) {
            throw LogicException::fromMessage('Not enough parameters');
        }

        $this->productsPaginator = $this->getProductsByProductIdOrShopsId($input->groupId, $input->productsId, $input->shopsId, $input->orderAsc);
        $this->productsPaginator ??= $this->getProductsByProductName($input->groupId, $input->productName, $input->orderAsc);
        $this->productsPaginator ??= $this->getProductByProductNameFilter($input->groupId, $input->productNameFilter, $input->orderAsc);
        $this->productsPaginator ??= $this->getProductByShopNameFilter($input->groupId, $input->shopNameFilter, $input->orderAsc);
        $this->productsPaginator ??= $this->getProductsByGroupId($input->groupId, $input->orderAsc);

        return $this->getProductsData($this->productsPaginator, $input->page, $input->pageItems);
    }

    public function getPagesTotal(): int
    {
        return $this->productsPaginator->getPagesTotal();
    }

    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     *
     * @return PaginatorInterface<int, Product>|null
     *
     * @throws DBNotFoundException
     */
    private function getProductsByProductIdOrShopsId(Identifier $groupId, array $productsId, array $shopsId, bool $orderAsc): ?PaginatorInterface
    {
        if (empty($productsId) && empty($shopsId)) {
            return null;
        }

        return $this->productRepository->findProductsOrFail(
            $groupId,
            empty($productsId) ? null : $productsId,
            empty($shopsId) ? null : $shopsId,
            $orderAsc
        );
    }

    /**
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    private function getProductsByGroupId(Identifier $groupId, bool $orderAsc): PaginatorInterface
    {
        return $this->productRepository->findProductsOrFail(
            $groupId,
            null,
            null,
            $orderAsc
        );
    }

    /**
     * @return PaginatorInterface<int, Product>|null
     *
     * @throws DBNotFoundException
     */
    private function getProductsByProductName(Identifier $groupId, NameWithSpaces $productName, bool $orderAsc): ?PaginatorInterface
    {
        if ($productName->isNull()) {
            return null;
        }

        return $this->productRepository->findProductsByProductNameOrFail($groupId, $productName, $orderAsc);
    }

    /**
     * @return PaginatorInterface<int, Product>|null
     *
     * @throws DBNotFoundException
     */
    private function getProductByProductNameFilter(Identifier $groupId, Filter $productNameFilter, bool $orderAsc): ?PaginatorInterface
    {
        if ($productNameFilter->isNull()) {
            return null;
        }

        return $this->productRepository->findProductsByProductNameFilterOrFail($groupId, $productNameFilter, $orderAsc);
    }

    /**
     * @return PaginatorInterface<int, Product>|null
     *
     * @throws DBNotFoundException
     */
    private function getProductByShopNameFilter(Identifier $groupId, Filter $shopNameFilter, bool $orderAsc): ?PaginatorInterface
    {
        if ($shopNameFilter->isNull()) {
            return null;
        }

        return $this->productRepository->findProductsByShopNameFilterOrFail($groupId, $shopNameFilter, $orderAsc);
    }

    /**
     * @param PaginatorInterface<int, Product> $productsPaginator
     *
     * @return array<int, array{
     *  id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  image: string|null,
     *  created_on: string
     * }>
     *
     * @throws DBNotFoundException
     */
    private function getProductsData(PaginatorInterface $productsPaginator, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $productsPaginator->setPagination($page->getValue(), $pageItems->getValue());

        return array_map(
            fn (Product $product): array => [
                'id' => $product->getId()->getValue(),
                'group_id' => $product->getGroupId()->getValue(),
                'name' => $product->getName()->getValue(),
                'description' => $product->getDescription()->getValue(),
                'image' => $product->getImage()->isNull()
                    ? null
                    : "{$this->appProtocolAndDomain}{$this->productPublicImagePath}/{$product->getImage()->getValue()}",
                'created_on' => $product->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            iterator_to_array($productsPaginator)
        );
    }
}

<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopGetData\Dto\ShopGetDataDto;

class ShopGetDataService
{
    private ?PaginatorInterface $shopsPaginator;

    public function __construct(
        private ShopRepositoryInterface $shopRepository,
        private string $appProtocolAndDomain,
        private string $shopPublicImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(ShopGetDataDto $input): array
    {
        if ($input->groupId->isNull()) {
            throw LogicException::fromMessage('Not enough parameters');
        }

        $this->shopsPaginator = $this->getShopsByShopIdAndProductId($input->groupId, $input->shopsId, $input->productsId, $input->orderAsc);
        $this->shopsPaginator ??= $this->getShopsByShopName($input->groupId, $input->shopName, $input->orderAsc);
        $this->shopsPaginator ??= $this->getShopsByShopNameFilter($input->groupId, $input->shopNameFilter, $input->orderAsc);
        $this->shopsPaginator ??= $this->getShopsByGroupId($input->groupId, $input->orderAsc);

        return $this->getShopsData($this->shopsPaginator, $input->page, $input->pageItems);
    }

    public function getPagesTotal(): int
    {
        return $this->shopsPaginator->getPagesTotal();
    }

    /**
     * @param Identifier[] $shopsId
     * @param Identifier[] $productsId
     *
     * @throws DBNotFoundException
     */
    private function getShopsByShopIdAndProductId(Identifier $groupId, array $shopsId, array $productsId, bool $orderAsc): ?PaginatorInterface
    {
        if (empty($shopsId) && empty($productsId)) {
            return null;
        }

        return $this->shopRepository->findShopsOrFail(
            $groupId,
            empty($shopsId) ? null : $shopsId,
            empty($productsId) ? null : $productsId,
            $orderAsc
        );
    }

    /**
     * @throws DBNotFoundException
     */
    private function getShopsByGroupId(Identifier $groupId, bool $orderAsc): ?PaginatorInterface
    {
        return $this->shopRepository->findShopsOrFail(
            $groupId,
            null,
            null,
            $orderAsc
        );
    }

    /**
     * @throws DBNotFoundException
     */
    private function getShopsByShopName(Identifier $groupId, NameWithSpaces $shopName, bool $orderAsc): ?PaginatorInterface
    {
        if ($shopName->isNull()) {
            return null;
        }

        return $this->shopRepository->findShopByShopNameOrFail($groupId, $shopName, $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getShopsByShopNameFilter(Identifier $groupId, Filter $shopNameFilter, bool $orderAsc): ?PaginatorInterface
    {
        if ($shopNameFilter->isNull()) {
            return null;
        }

        return $this->shopRepository->findShopByShopNameFilterOrFail($groupId, $shopNameFilter, $orderAsc);
    }

    private function getShopsData(PaginatorInterface $shopsPaginator, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $shopsPaginator->setPagination($page->getValue(), $pageItems->getValue());

        return array_map(
            fn (Shop $shop) => [
                'id' => $shop->getId()->getValue(),
                'group_id' => $shop->getGroupId()->getValue(),
                'name' => $shop->getName()->getValue(),
                'description' => $shop->getDescription()->getValue(),
                'image' => $shop->getImage()->isNull()
                    ? null
                    : "{$this->appProtocolAndDomain}{$this->shopPublicImagePath}/{$shop->getImage()->getValue()}",
                'created_on' => $shop->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            iterator_to_array($shopsPaginator)
        );
    }
}

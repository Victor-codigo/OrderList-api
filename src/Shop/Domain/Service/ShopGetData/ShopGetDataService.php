<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetData;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopGetData\Dto\ShopGetDataDto;

class ShopGetDataService
{
    public function __construct(
        private ShopRepositoryInterface $shopRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ShopGetDataDto $input): array
    {
        $shopsPaginator = $this->shopRepository->findShopsOrFail(
            empty($input->shopsId) ? null : $input->shopsId,
            $input->groupId->isNull() ? null : $input->groupId,
            empty($input->productsId) ? null : $input->productsId,
            $input->shopName,
            $input->shopFilter,
            $input->orderAsc
        );

        return $this->getShopsData($shopsPaginator, $input->page, $input->pageItems);
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
                'image' => $shop->getImage()->getValue(),
                'created_on' => $shop->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            iterator_to_array($shopsPaginator)
        );
    }
}

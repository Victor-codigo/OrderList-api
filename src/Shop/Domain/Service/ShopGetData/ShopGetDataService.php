<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetData;

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
        $shops = $this->shopRepository->findShopsOrFail(
            empty($input->shopsId) ? null : $input->shopsId,
            $input->groupId->isNull() ? null : $input->groupId,
            empty($input->productsId) ? null : $input->productsId,
            $input->shopName,
            empty($input->shopNameStartsWith) ? null : $input->shopNameStartsWith,
        );

        return $this->getShopsData($shops, $input->shopsMaxNumber);
    }

    private function getShopsData(PaginatorInterface $shops, int $shopsMaxNumber): array
    {
        $shops->setPagination(1, $shopsMaxNumber);
        $shopsData = [];

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $shopsData[] = [
                'id' => $shop->getId()->getValue(),
                'group_id' => $shop->getGroupId()->getValue(),
                'name' => $shop->getName()->getValue(),
                'description' => $shop->getDescription()->getValue(),
                'image' => $shop->getImage()->getValue(),
                'created_on' => $shop->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }

        return $shopsData;
    }
}

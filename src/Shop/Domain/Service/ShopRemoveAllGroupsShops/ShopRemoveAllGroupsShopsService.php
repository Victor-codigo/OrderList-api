<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopRemoveAllGroupsShops;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsDto;

class ShopRemoveAllGroupsShopsService
{
    private const SHOP_PAGINATION_PAGE_ITEMS = 100;

    public function __construct(
        private ShopRepositoryInterface $shopRepository,
        private EntityImageRemoveService $entityImageRemoveService,
        private string $shopImagePath
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ShopRemoveAllGroupsShopsDto $input): array
    {
        $shopsPaginator = $this->shopRepository->findGroupsShopsOrFail($input->groupsId);

        $shopsId = [];
        foreach ($shopsPaginator->getAllPages(self::SHOP_PAGINATION_PAGE_ITEMS) as $shopsIterator) {
            $shops = iterator_to_array($shopsIterator);
            $shopsId[] = array_map(
                fn (Shop $shop) => $shop->getId(),
                $shops
            );

            $this->removeShopsImages($shops);
            $this->shopRepository->remove($shops);
        }

        return array_merge(...$shopsId);
    }

    /**
     * @param Shop[] $shops
     *
     * @throws DomainInternalErrorException
     */
    private function removeShopsImages(array $shops): void
    {
        $shopImagePath = ValueObjectFactory::createPath($this->shopImagePath);

        foreach ($shops as $shop) {
            $this->entityImageRemoveService->__invoke($shop, $shopImagePath);
        }
    }
}

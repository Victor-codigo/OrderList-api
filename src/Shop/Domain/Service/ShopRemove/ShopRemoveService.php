<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopRemove\Dto\ShopRemoveDto;

class ShopRemoveService
{
    public function __construct(
        private ShopRepositoryInterface $shopRepository,
        private string $shopImagePath
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     * @throws DBConnectionException
     */
    public function __invoke(ShopRemoveDto $input): array
    {
        $shopsToRemove = $this->shopRepository->findShopsOrFail($input->groupId, $input->shopsId);
        /** @var Shop[] $shopsToRemove */
        $shopsToRemove = iterator_to_array($shopsToRemove);

        array_map(
            fn (Shop $shop) => $this->removeImage($shop->getImage()),
            $shopsToRemove
        );

        $this->shopRepository->remove($shopsToRemove);

        return array_map(
            fn (Shop $shop): Identifier => $shop->getId(),
            $shopsToRemove
        );
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

        $imagePath = $this->shopImagePath."/{$imagePath}";

        if (!file_exists($imagePath)) {
            return;
        }

        if (!unlink($imagePath)) {
            throw DomainInternalErrorException::fromMessage('The image cannot be deleted');
        }
    }
}

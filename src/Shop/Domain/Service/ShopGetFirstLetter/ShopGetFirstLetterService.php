<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopGetFirstLetter\Dto\ShopGetFirstLetterDto;

class ShopGetFirstLetterService
{
    public function __construct(
        private ShopRepositoryInterface $shopRepository,
    ) {
    }

    /**
     * @return array<int, string>
     *
     * @throws DBNotFoundException
     */
    public function __invoke(ShopGetFirstLetterDto $input): array
    {
        return $this->shopRepository->findGroupShopsFirstLetterOrFail($input->groupId);
    }
}

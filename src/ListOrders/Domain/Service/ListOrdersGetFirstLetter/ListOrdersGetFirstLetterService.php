<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterDto;

class ListOrdersGetFirstLetterService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @return array<int, string>
     *
     * @throws DBNotFoundException
     */
    public function __invoke(ListOrdersGetFirstLetterDto $input): array
    {
        return $this->listOrdersRepository->findGroupListOrdersFirstLetterOrFail($input->groupId);
    }
}

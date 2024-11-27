<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersModify\Dto\ListOrdersModifyDto;
use ListOrders\Domain\Service\ListOrdersModify\Exception\ListOrdersModifyNameAlreadyExistsInGroupException;

class ListOrdersModifyService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersModifyDto $input): ListOrders
    {
        if ($this->hasGroupListOrdersWithSameName($input->groupId, $input->listOrdersId, $input->name)) {
            throw ListOrdersModifyNameAlreadyExistsInGroupException::fromMessage('The group name is already in use in this group');
        }

        $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$input->listOrdersId], $input->groupId);
        /** @var ListOrders $listOrders */
        $listOrders = iterator_to_array($listOrdersPagination)[0];

        $listOrders
            ->setUserId($input->userId)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setDateToBuy($input->dateToBuy);

        $this->listOrdersRepository->save([$listOrders]);

        return $listOrders;
    }

    private function hasGroupListOrdersWithSameName(Identifier $groupId, Identifier $listOrdersId, NameWithSpaces $listOrdersName): bool
    {
        $listOrders = $this->getGroupListOrders($groupId, $listOrdersName);

        if (null === $listOrders) {
            return false;
        }

        if ($listOrdersId->equalTo($listOrders->getId())) {
            return false;
        }

        return true;
    }

    private function getGroupListOrders(Identifier $groupId, NameWithSpaces $listOrdersName): ?ListOrders
    {
        try {
            return $this->listOrdersRepository->findListOrdersByNameOrFail($listOrdersName, $groupId);
        } catch (DBNotFoundException) {
            return null;
        }
    }
}

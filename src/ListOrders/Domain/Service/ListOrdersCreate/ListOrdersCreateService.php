<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreate\Dto\ListOrdersCreateDto;
use ListOrders\Domain\Service\ListOrdersCreate\Exception\ListOrdersCreateNameAlreadyExistsInGroupException;

class ListOrdersCreateService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     * @throws ListOrdersCreateNameAlreadyExistsInGroupException
     */
    public function __invoke(ListOrdersCreateDto $input): ListOrders
    {
        if ($this->hasGroupListOrdersWithSameName($input->groupId, $input->name)) {
            throw ListOrdersCreateNameAlreadyExistsInGroupException::fromMessage('The group name is already in use in this group');
        }

        $listOrders = $this->createListOrders($input->groupId, $input->userId, $input->name, $input->description, $input->dateToBuy);

        $this->listOrdersRepository->save([$listOrders]);

        return $listOrders;
    }

    private function hasGroupListOrdersWithSameName(Identifier $groupId, NameWithSpaces $listOrdersName): bool
    {
        $listOrders = $this->getGroupListOrders($groupId, $listOrdersName);

        if (null === $listOrders) {
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

    private function createListOrders(Identifier $groupId, Identifier $userId, NameWithSpaces $name, Description $description, DateNowToFuture $dateToBuy): ListOrders
    {
        $listOrdersId = $this->listOrdersRepository->generateId();

        return new ListOrders(
            ValueObjectFactory::createIdentifier($listOrdersId),
            $groupId,
            $userId,
            $name,
            $description,
            $dateToBuy
        );
    }
}

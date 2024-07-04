<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreateFrom;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Dto\ListOrdersCreateFromDto;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromListOrdersIdNotFoundException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromNameAlreadyExistsException;
use Order\Domain\Model\Order;

class ListOrdersCreateFromService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @throws ListOrdersCreateFromListOrdersIdNotFoundException
     * @throws ListOrdersCreateFromNameAlreadyExistsException
     * @throws DBConnectionException
     * @throws DBUniqueConstraintException
     */
    public function __invoke(ListOrdersCreateFromDto $input): ListOrders
    {
        $listOrders = $this->getListOrdersIdCreateFrom($input->listOrdersIdCreateFrom, $input->groupId);
        $this->validateListOrdersNewName($input->groupId, $input->name);
        $listOrdersNew = $this->createListOrdersNew($listOrders, $input->userId, $input->name);
        $this->listOrdersRepository->saveListOrdersAndOrders($listOrdersNew);

        return $listOrdersNew;
    }

    /**
     * @throws ListOrdersCreateFromListOrdersIdNotFoundException
     */
    private function getListOrdersIdCreateFrom(Identifier $listOrdersIdCreateFrom, Identifier $groupId): ListOrders
    {
        try {
            $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersIdCreateFrom], $groupId);
            $listOrdersPagination->setPagination(1, 1);

            return iterator_to_array($listOrdersPagination)[0];
        } catch (DBNotFoundException) {
            throw ListOrdersCreateFromListOrdersIdNotFoundException::fromMessage('List orders id not found');
        }
    }

    /**
     * @throws ListOrdersCreateFromNameAlreadyExistsException
     */
    private function validateListOrdersNewName(Identifier $groupId, NameWithSpaces $name): void
    {
        try {
            $filterText = ValueObjectFactory::createFilter(
                'listOrdersName',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $name
            );
            $this->listOrdersRepository->findListOrderByListOrdersNameFilterOrFail($groupId, $filterText, true);

            throw ListOrdersCreateFromNameAlreadyExistsException::fromMessage('ListOrders name already exists in data base');
        } catch (DBNotFoundException) {
        }
    }

    private function createListOrdersNew(ListOrders $listOrdersOld, Identifier $userId, NameWithSpaces $name): ListOrders
    {
        $listOrdersNew = $this->createListOrdersNewFrom($listOrdersOld, $userId, $name);
        $orders = $this->copyListOrdersOrders($listOrdersOld, $listOrdersNew, $userId);
        $listOrdersNew->setOrders($orders);

        return $listOrdersNew;
    }

    private function createListOrdersNewFrom(ListOrders $listOrdersCreateFrom, Identifier $userId, NameWithSpaces $name): ListOrders
    {
        return new ListOrders(
            ValueObjectFactory::createIdentifier($this->listOrdersRepository->generateId()),
            $listOrdersCreateFrom->getGroupId(),
            $userId,
            $name,
            $listOrdersCreateFrom->getDescription(),
            ValueObjectFactory::createDateNowToFuture(null)
        );
    }

    /**
     * @return Order[]
     */
    private function copyListOrdersOrders(ListOrders $listOrdersOld, ListOrders $listOrdersNew, Identifier $userId): array
    {
        return array_map(
            function (Order $order) use ($listOrdersNew, $userId) {
                $orderNew = $order->cloneWithNewId(ValueObjectFactory::createIdentifier($this->listOrdersRepository->generateId()));
                $orderNew->setUserId($userId);
                $orderNew->setBought(false);
                $orderNew->setListOrders($listOrdersNew);

                return $orderNew;
            },
            $listOrdersOld->getOrders()->toArray()
        );
    }
}

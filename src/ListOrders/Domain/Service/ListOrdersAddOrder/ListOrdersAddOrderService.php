<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersAddOrder;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Application\ListOrdersAddOrder\Dto\OrderBoughtDto;
use ListOrders\Application\ListOrdersAddOrder\Exception\ListOrdersAddOrderAllOrdersArAlreadyInListOrdersException;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersAddOrder\Dto\ListOrdersAddOrderDto;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrdersListOrderNotFoundException;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrdersOrdersNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;

class ListOrdersAddOrderService
{
    public function __construct(
        private ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository,
        private ListOrdersRepositoryInterface $listOrdersRepository,
        private OrderRepositoryInterface $ordersRepository,
    ) {
    }

    /**
     * @return ListOrdersOrders[]
     *
     * @throws ListOrdersAddOrderAllOrdersArAlreadyInListOrdersException
     * @throws ListOrdersAddOrdersListOrderNotFoundException
     * @throws ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException
     */
    public function __invoke(ListOrdersAddOrderDto $input): array
    {
        $ordersBoughtUnique = $this->ordersBoughtUnique($input->ordersBought);
        $listOrders = $this->getListOrders($input->listOrdersId, $input->groupId);
        $listOrdersOrders = $this->getListOrdersOrders($input->listOrdersId, $input->groupId);
        $ordersDb = $this->getOrders($ordersBoughtUnique, $input->groupId);
        $ordersBoughtToSave = $this->getOrdersBoughtValid($ordersBoughtUnique, $listOrdersOrders, $ordersDb);
        $listOrdersOrdersToSave = $this->createListOrdersOrders($listOrders, $ordersDb, $ordersBoughtToSave);

        $this->listOrdersOrdersRepository->save($listOrdersOrdersToSave);

        return $listOrdersOrdersToSave;
    }

    /**
     * @return ListOrdersOrders[]
     */
    private function getListOrdersOrders(Identifier $listOrdersId, Identifier $groupId): array
    {
        try {
            $listOrdersOrdersPaginator = $this->listOrdersOrdersRepository->findListOrderOrdersByIdOrFail([$listOrdersId], $groupId);
            $listOrdersOrdersPaginator->setPagination(1, 100);

            return iterator_to_array($listOrdersOrdersPaginator);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @throws ListOrdersAddOrdersListOrderNotFoundException
     */
    private function getListOrders(Identifier $listOrdersId, Identifier $groupId): ListOrders
    {
        try {
            $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersId], $groupId);
            $listOrdersPaginator->setPagination(1, 1);

            return iterator_to_array($listOrdersPaginator)[0];
        } catch (DBNotFoundException) {
            throw ListOrdersAddOrdersListOrderNotFoundException::fromMessage('List order not found');
        }
    }

    /**
     * @return Order[]
     *
     * @throws ListOrdersAddOrdersListOrderNotFoundException
     */
    private function getOrders(array $ordersBought, Identifier $groupId): array
    {
        try {
            $ordersBoughtId = array_map(
                fn (OrderBoughtDto $orderBought) => $orderBought->orderId,
                $ordersBought
            );
            $ordersPaginator = $this->ordersRepository->findOrdersByIdOrFail($ordersBoughtId, $groupId);
            $ordersPaginator->setPagination(1, 100);

            return iterator_to_array($ordersPaginator);
        } catch (DBNotFoundException) {
            throw ListOrdersAddOrdersOrdersNotFoundException::fromMessage('Orders not found');
        }
    }

    /**
     * @param OrderBoughtDto[]   $ordersBought
     * @param ListOrdersOrders[] $listOrdersOrders
     * @param Order[]            $ordersDb
     *
     * @return OrderBoughtDto[]
     *
     * @throws ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException
     */
    private function getOrdersBoughtValid(array $ordersBought, array $listOrdersOrders, array $ordersDb): array
    {
        $ordersBoughtValid = $this->ordersBoughtFilterByValidOrderId($ordersBought, $ordersDb);

        return $this->ordersBoughtFilterByNotAlreadyInListOrders($ordersBoughtValid, $listOrdersOrders);
    }

    /**
     * @param OrderBoughtDto[] $orderBought
     * @param Order[]          $ordersDb
     *
     * @return OrderBoughtDto[]
     */
    private function ordersBoughtFilterByValidOrderId(array $orderBought, array $ordersDb): array
    {
        $ordersDbId = array_map(
            fn (Order $listOrdersOrder) => $listOrdersOrder->getId(),
            $ordersDb
        );

        return array_filter(
            $orderBought,
            fn (OrderBoughtDto $orderBought) => in_array($orderBought->orderId, $ordersDbId)
        );
    }

    /**
     * @param OrderBoughtDto[]   $ordersBought
     * @param ListOrdersOrders[] $listOrdersOrders
     *
     * @return OrderBoughtDto[]
     *
     * @throws ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException
     */
    private function ordersBoughtFilterByNotAlreadyInListOrders(array $ordersBought, array $listOrdersOrders): array
    {
        $listOrdersOrdersId = array_map(
            fn (ListOrdersOrders $listOrdersOrder) => $listOrdersOrder->getOrderId(),
            $listOrdersOrders
        );

        $ordersBoughtNotInListOrders = array_filter(
            $ordersBought,
            fn (OrderBoughtDto $orderBoughtValid) => !in_array($orderBoughtValid->orderId, $listOrdersOrdersId)
        );

        if (empty($ordersBoughtNotInListOrders)) {
            throw ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException::fromMessage('All orders are already in the list of orders');
        }

        return $ordersBoughtNotInListOrders;
    }

    /**
     * @param OrderBoughtDto[] $orderBought
     *
     * @return OrderBoughtDto[]
     */
    private function ordersBoughtUnique(array $orderBought): array
    {
        $ordersBoughtUnique = [];
        foreach ($orderBought as $orderBought) {
            $ordersBoughtUnique[$orderBought->orderId->getValue()] = $orderBought;
        }

        return array_values($ordersBoughtUnique);
    }

    /**
     * @param OrderBoughtDto[] $ordersBoughtToSave
     *
     * @return ListOrdersOrders[]
     */
    private function createListOrdersOrders(ListOrders $listOrders, array $ordersBd, array $ordersBoughtToSave): array
    {
        $ordersBdById = array_combine(
            array_map(fn (Order $orderDb) => $orderDb->getId()->getValue(),
                $ordersBd
            ),
            $ordersBd
        );

        return array_map(
            fn (OrderBoughtDto $orderBought) => $this->createListOrdersOrder($listOrders, $ordersBdById[$orderBought->orderId->getValue()], $orderBought->bought),
            $ordersBoughtToSave
        );
    }

    private function createListOrdersOrder(ListOrders $listOrders, Order $order, bool $bought): ListOrdersOrders
    {
        $listOrdersId = $this->listOrdersOrdersRepository->generateId();

        return new ListOrdersOrders(
            ValueObjectFactory::createIdentifier($listOrdersId),
            $order->getId(),
            $listOrders->getId(),
            $bought,
            $listOrders,
            $order
        );
    }
}

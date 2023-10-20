<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetOrders;

use Common\Domain\Exception\LogicException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetOrders\Dto\ListOrdersGetOrdersDto;
use Order\Domain\Model\Order;

class ListOrdersGetOrdersService
{
    private PaginatorInterface $listOrderOrdersPaginator;

    public function __construct(
        private ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ListOrdersGetOrdersDto $input): array
    {
        $this->listOrderOrdersPaginator = $this->listOrdersOrdersRepository->findListOrderOrdersDataByIdOrFail($input->listOrderId, $input->groupId);
        $this->listOrderOrdersPaginator->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getOrderData($this->listOrderOrdersPaginator);
    }

    /**
     * @throws LogicException
     */
    public function getPaginationTotalPages(): int
    {
        if (!isset($this->listOrderOrdersPaginator)) {
            throw LogicException::fromMessage('Paginator is not initialized. Call first method __invoke.');
        }

        return $this->listOrderOrdersPaginator->getPagesTotal();
    }

    private function getOrderData(PaginatorInterface $listOrderOrdersPaginator): array
    {
        return array_map(
            fn (Order $order) => [
                'id' => $order->getId()->getValue(),
                'user_id' => $order->getUserId()->getValue(),
                'group_id' => $order->getGroupId()->getValue(),
                'description' => $order->getDescription()->getValue(),
                'amount' => $order->getAmount()->getValue(),
                'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
                'product' => [
                    'id' => $order->getProduct()->getId()->getValue(),
                    'name' => $order->getProduct()->getName()->getValue(),
                    'description' => $order->getProduct()->getDescription()->getValue(),
                    'image' => $order->getProduct()->getImage()->getValue(),
                    'created_on' => $order->getProduct()->getCreatedOn()->format('Y-m-d H:i:s'),
                ],
                'shop' => [
                    'id' => $order->getShop()->getId()->getValue(),
                    'name' => $order->getShop()->getName()->getValue(),
                    'description' => $order->getShop()->getDescription()->getValue(),
                    'image' => $order->getShop()->getImage()->getValue(),
                    'created_on' => $order->getShop()->getCreatedOn()->format('Y-m-d H:i:s'),
                ],
            ],
            iterator_to_array($listOrderOrdersPaginator)
        );
    }
}

<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Product\Domain\Model\ProductShop;

class OrderGetDataService
{
    private ?PaginatorInterface $ordersPaginator;

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private string $productPublicImagePath,
        private string $shopPublicImagePath,
        private string $appProtocolAndDomain
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(OrderGetDataDto $input): array
    {
        $this->ordersPaginator = $this->getOrdersByOrdersId($input->groupId, $input->ordersId, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByProductName($input->groupId, $input->listOrdersId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByShopName($input->groupId, $input->listOrdersId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByListOrdersName($input->groupId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByListOrdersId($input->groupId, $input->listOrdersId, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByGroupId($input->groupId, $input->filterSection, $input->orderAsc);

        return $this->getOrdersData($input->page, $input->pageItems);
    }

    public function getPagesTotal(): int
    {
        return $this->ordersPaginator->getPagesTotal();
    }

    private function getOrdersData(PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $this->ordersPaginator->setPagination($page->getValue(), $pageItems->getValue());
        $orders = iterator_to_array($this->ordersPaginator);

        return array_map(
            fn (Order $order) => $this->getOrderData($order),
            $orders
        );
    }

    /**
     * @param Identifier[] $ordersId
     *
     * @throws DBNotFoundException
     */
    private function getOrdersByOrdersId(Identifier $groupId, array $ordersId, bool $orderAsc): ?PaginatorInterface
    {
        if (empty($ordersId)) {
            return null;
        }

        return $this->orderRepository->findOrdersByIdOrFail($groupId, $ordersId, $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersByGroupId(Identifier $groupId, ?Filter $filterSection, bool $orderAsc): ?PaginatorInterface
    {
        return $this->orderRepository->findOrdersByGroupIdOrFail($groupId, $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersByProductName(Identifier $groupId, IdentifierNullable $listOrdersId, ?Filter $filterSection, ?Filter $filterText, bool $orderAsc): ?PaginatorInterface
    {
        if (null === $filterSection || null === $filterText) {
            return null;
        }

        if ($filterSection->isNull() || $filterText->isNull()) {
            return null;
        }

        if (FILTER_SECTION::PRODUCT !== $filterSection->getFilter()->getValue()
        && FILTER_SECTION::ORDER !== $filterSection->getFilter()->getValue()) {
            return null;
        }

        return $this->orderRepository->findOrdersByProductNameFilterOrFail($groupId, $listOrdersId->toIdentifier(), $filterText, $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersByShopName(Identifier $groupId, IdentifierNullable $listOrdersId, ?Filter $filterSection, ?Filter $filterText, bool $orderAsc): ?PaginatorInterface
    {
        if (null === $filterSection || null === $filterText) {
            return null;
        }

        if ($filterSection->isNull() || $filterText->isNull()) {
            return null;
        }

        if (FILTER_SECTION::SHOP !== $filterSection->getFilter()->getValue()) {
            return null;
        }

        return $this->orderRepository->findOrdersByShopNameFilterOrFail($groupId, $listOrdersId->toIdentifier(), $filterText, $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersByListOrdersName(Identifier $groupId, ?Filter $filterSection, ?Filter $filterText, bool $orderAsc): ?PaginatorInterface
    {
        if (null === $filterSection || null === $filterText) {
            return null;
        }

        if ($filterSection->isNull() || $filterText->isNull()) {
            return null;
        }

        if (FILTER_SECTION::LIST_ORDERS !== $filterSection->getFilter()->getValue()) {
            return null;
        }

        return $this->orderRepository->findOrdersByListOrdersNameOrFail($groupId, ValueObjectFactory::createNameWithSpaces($filterText->getValue()), $orderAsc);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersByListOrdersId(Identifier $groupId, IdentifierNullable $listOrdersId, bool $orderAsc): ?PaginatorInterface
    {
        if ($listOrdersId->isNull()) {
            return null;
        }

        return $this->orderRepository->findOrdersByListOrdersIdOrFail($listOrdersId->toIdentifier(), $groupId, $orderAsc);
    }

    private function getOrderData(Order $order): array
    {
        return [
            'id' => $order->getId()->getValue(),
            'group_id' => $order->getGroupId()->getValue(),
            'list_orders_id' => $order->getListOrdersId()->getValue(),
            'user_id' => $order->getUserId()->getValue(),
            'description' => $order->getDescription()->getValue(),
            'amount' => $order->getAmount()->getValue(),
            'bought' => $order->getBought(),
            'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
            'product' => [
                'id' => $order->getProduct()->getId()->getValue(),
                'name' => $order->getProduct()->getName()->getValue(),
                'description' => $order->getProduct()->getDescription()->getValue(),
                'image' => $order->getProduct()->getImage()->isNull()
                    ? null
                    : "{$this->appProtocolAndDomain}{$this->productPublicImagePath}/{$order->getProduct()->getImage()->getValue()}",
                'created_on' => $order->getProduct()->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            'shop' => $this->getProductShopData($order),
            'productShop' => $this->getProductShopPrice($order),
        ];
    }

    /**
     * @return array<{id: string, name: string, description: string, created_on: string}>
     */
    private function getProductShopData(Order $order): array
    {
        if ($order->getShopId()->isNull()) {
            return [];
        }

        return [
            'id' => $order->getShop()->getId()->getValue(),
            'name' => $order->getShop()->getName()->getValue(),
            'description' => $order->getShop()->getDescription()->getValue(),
            'image' => $order->getShop()->getImage()->isNull()
                ? null
                : "{$this->appProtocolAndDomain}{$this->shopPublicImagePath}/{$order->getShop()->getImage()->getValue()}",
            'created_on' => $order->getShop()->getCreatedOn()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<{price: float, unit: string}>
     */
    private function getProductShopPrice(Order $order): array
    {
        if ($order->getShopId()->isNull()) {
            return [];
        }

        /** @var ProductShop[] $productsShops */
        $productsShops = $order->getProduct()->getProductShop()->getValues();

        foreach ($productsShops as $productShop) {
            if ($productShop->getShopId()->equalTo($order->getShopId())) {
                return [
                    'price' => $productShop->getPrice()->getValue(),
                    'unit' => $productShop->getUnit()->getValue(),
                ];
            }
        }

        return [];
    }
}

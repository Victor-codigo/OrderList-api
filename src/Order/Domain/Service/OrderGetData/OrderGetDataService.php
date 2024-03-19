<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderGetData;

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
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;

class OrderGetDataService
{
    private ?PaginatorInterface $ordersPaginator;

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductShopRepositoryInterface $productShopRepository,
    ) {
    }

    public function __invoke(OrderGetDataDto $input): array
    {
        $this->ordersPaginator = $this->getOrdersByOrdersId($input->groupId, $input->ordersId, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByProductName($input->groupId, $input->listOrdersId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByShopName($input->groupId, $input->listOrdersId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByListOrdersName($input->groupId, $input->filterSection, $input->filterText, $input->orderAsc);
        $this->ordersPaginator ??= $this->getOrdersByGroupId($input->groupId, $input->orderAsc);

        return $this->getOrdersData($input->groupId, $input->page, $input->pageItems);
    }

    public function getPagesTotal(): int
    {
        return $this->ordersPaginator->getPagesTotal();
    }

    private function getOrdersData(Identifier $groupId, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $this->ordersPaginator->setPagination($page->getValue(), $pageItems->getValue());
        $orders = iterator_to_array($this->ordersPaginator);

        $productsShops = $this->getProductsPricesByProductId($groupId, $orders);

        return array_map(
            fn (Order $order) => $this->createOrderData($order, $productsShops),
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
    private function getOrdersByGroupId(Identifier $groupId, bool $orderAsc): PaginatorInterface
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
     * @param Order[] $orders
     *
     * @return ProductShop[]
     *
     * @throws DBNotFoundException
     */
    private function getProductsPricesByProductId(Identifier $groupId, array $orders): array
    {
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $productsShopsPagination = $this->productShopRepository->findProductsAndShopsOrFail($productsId, $shopsId, $groupId);

        return iterator_to_array($productsShopsPagination);
    }

    /**
     * @param ProductShop[] $productsShops
     */
    private function createOrderData(Order $order, array $productsShops): array
    {
        $productShopFind = array_filter(
            $productsShops,
            fn (ProductShop $productShop) => $productShop->getProductId()->equalTo($order->getProductId())
                                          && $productShop->getShopId()->equalTo($order->getShopId())
        );

        $price = null;
        $unit = null;
        if (count($productShopFind) > 0) {
            $productShop = reset($productShopFind);
            $price = $productShop->getPrice()->getValue();
            $unit = $productShop->getUnit()->getValue();
        }

        return [
            'id' => $order->getId()->getValue(),
            'product_id' => $order->getProductId()->getValue(),
            'shop_id' => $order->getShopId()->getValue(),
            'user_id' => $order->getUserId()->getValue(),
            'group_id' => $order->getGroupId()->getValue(),
            'description' => $order->getDescription()->getValue(),
            'amount' => $order->getAmount()->getValue(),
            'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
            'price' => $price,
            'unit' => $unit,
        ];
    }
}

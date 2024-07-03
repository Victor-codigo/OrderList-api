<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderModify;

use DateTime;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderModify\Dto\OrderModifyDto;
use Order\Domain\Service\OrderModify\Exception\OrderModifyListOrdersIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductShopRepeatedException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyShopIdNotFoundException;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderModifyService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ListOrdersRepositoryInterface $listOrdersRepository,
        private ProductRepositoryInterface $productRepository,
        private ShopRepositoryInterface $shopRepository
    ) {
    }

    /**
     * @throws OrderModifyListOrdersIdNotFoundException
     * @throws OrderModifyProductIdNotFoundException
     * @throws OrderModifyShopIdNotFoundException
     * @throws OrderCreateProductShopRepeatedException
     * @throws DBNotFoundException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(OrderModifyDto $input): Order
    {
        $listOrders = $this->validateListOrders($input->groupId, $input->listOrdersId);
        $product = $this->validateProduct($input->groupId, $input->productId);
        $shop = $this->validateShop($input->groupId, $input->shopId->toIdentifier(), $input->productId);
        $this->productAndShopAreNotRepeatedOrFail($input->orderId, $input->groupId, $input->listOrdersId, $product, $shop);

        /** @var Order $order */
        $order = iterator_to_array(
            $this->orderRepository->findOrdersByIdOrFail($input->groupId, [$input->orderId], true)
        )[0];

        $order
            ->setAmount($input->amount)
            ->setDescription($input->description)
            ->setUserId($input->userId)
            ->setListOrders($listOrders)
            ->setProduct($product)
            ->setShop($shop)
            ->setCreatedOn(new DateTime());

        $this->orderRepository->save([$order]);

        return $order;
    }

    /**
     * @throws OrderCreateProductShopRepeatedException
     */
    private function productAndShopAreNotRepeatedOrFail(Identifier $orderId, Identifier $groupId, Identifier $listOrdersId, Product $product, ?Shop $shop): void
    {
        try {
            $shopId = null === $shop ? [] : [$shop->getId()];
            $ordersPagination = $this->orderRepository->findOrdersByListOrdersIdProductIdAndShopIdOrFail($groupId, $listOrdersId, [$product->getId()], $shopId);
            $ordersPagination->setPagination(1, 1);
            /** @var Order $order */
            $order = iterator_to_array($ordersPagination)[0];

            if ($order->getId()->equalTo($orderId)) {
                throw DBNotFoundException::fromMessage('Order is the same order as modified');
            }

            throw OrderModifyProductShopRepeatedException::fromMessage('Product and shop are already in the list of orders');
        } catch (DBNotFoundException) {
        }
    }

    /**
     * @throws OrderModifyListOrdersIdNotFoundException
     */
    private function validateListOrders(Identifier $groupId, Identifier $listOrdersId): ListOrders
    {
        try {
            $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersId], $groupId);

            return iterator_to_array($listOrdersPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderModifyListOrdersIdNotFoundException::fromMessage('Product id not found');
        }
    }

    /**
     * @throws OrderModifyProductIdNotFoundException
     */
    private function validateProduct(Identifier $groupId, Identifier $productId): Product
    {
        try {
            $productPagination = $this->productRepository->findProductsOrFail($groupId, [$productId]);

            return iterator_to_array($productPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderModifyProductIdNotFoundException::fromMessage('Product id not found');
        }
    }

    /**
     * @throws OrderModifyShopIdNotFoundException
     */
    private function validateShop(Identifier $groupId, Identifier $shopId, Identifier $productId): ?Shop
    {
        try {
            if ($shopId->isNull()) {
                return null;
            }

            $shopPagination = $this->shopRepository->findShopsOrFail($groupId, [$shopId], [$productId]);

            return iterator_to_array($shopPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderModifyShopIdNotFoundException::fromMessage('Shop id not found');
        }
    }
}

<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderModify\Dto\OrderModifyDto;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyShopIdNotFoundException;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderModifyService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private ShopRepositoryInterface $shopRepository
    ) {
    }

    /**
     * @throws OrderModifyProductIdNotFoundException
     * @throws OrderModifyShopIdNotFoundException
     * @throws DBNotFoundException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(OrderModifyDto $input): Order
    {
        $product = $this->validateProduct($input->groupId, $input->productId);

        $shop = null;
        if (!$input->shopId->isNull()) {
            $shop = $this->validateShopAndProductIsinAShop($input->groupId, $input->shopId->toIdentifier(), $input->productId);
        }

        /** @var Order $order */
        $order = iterator_to_array(
            $this->orderRepository->findOrdersByIdOrFail([$input->orderId], $input->groupId)
        )[0];

        $order
            ->setAmount($input->amount)
            ->setUnit($input->unit)
            ->setDescription($input->description)
            ->setUserId($input->userId)
            ->setProduct($product)
            ->setShop($shop)
            ->setCreatedOn(new \DateTime());

        $this->orderRepository->save([$order]);

        return $order;
    }

    /**
     * @throws OrderModifyProductIdNotFoundException
     */
    private function validateProduct(identifier $groupId, Identifier $productId): Product
    {
        try {
            $productPagination = $this->productRepository->findProductsOrFail([$productId], $groupId);

            return iterator_to_array($productPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderModifyProductIdNotFoundException::fromMessage('Product id not found');
        }
    }

    /**
     * @throws OrderModifyShopIdNotFoundException
     */
    private function validateShopAndProductIsinAShop(Identifier $groupId, Identifier $shopId, Identifier $productId): Shop
    {
        try {
            $shopPagination = $this->shopRepository->findShopsOrFail([$shopId], $groupId, [$productId]);

            return iterator_to_array($shopPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderModifyShopIdNotFoundException::fromMessage('Shop id not found');
        }
    }
}

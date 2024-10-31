<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Product\Domain\Model\ProductShop;

readonly class ShareListOrdersGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, array{
     *  order: Order,
     *  productShop: ProductShop,
     * }> $orders
     */
    public function __construct(
        public ListOrders $listOrders,
        public array $orders,
        public PaginatorPage $page,
        public int $pagesTotal,
    ) {
    }

    /**
     * @return array{
     *  list_orders: array{
     *      id: string|null,
     *      group_id: string|null,
     *      user_id: string|null,
     *      name: string|null,
     *      description: string|null,
     *      date_to_buy: string|null
     *  },
     *  orders: array<int, array{
     *      id: string|null,
     *      group_id: string|null,
     *      list_orders_id: string|null,
     *      user_id: string|null,
     *      description: string|null,
     *      amount: float|null,
     *      bought: bool,
     *      created_on: string,
     *      product: array{
     *          id: string|null,
     *          name: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      },
     *      shop: array{}|array{
     *          id: string|null,
     *          name: string|null,
     *          address: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      },
     *      productShop: array{}|array{
     *          price: float|null,
     *          unit: string|null
     *      }
     *  }>
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        $ordersData = [];
        foreach ($this->orders as $orderAndPrice) {
            $order = $orderAndPrice['order'];
            $price = $orderAndPrice['productShop'];

            $shopData = [];
            if (null !== $order->getShop()) {
                $shopData = [
                    'id' => $order->getShop()->getId()->getValue(),
                    'name' => $order->getShop()->getName()->getValue(),
                    'address' => $order->getShop()->getAddress()->getValue(),
                    'description' => $order->getShop()->getDescription()->getValue(),
                    'image' => $order->getShop()->getImage()->getValue(),
                    'created_on' => $order->getShop()->getCreatedOn()->format('Y-m-d H:i:s'),
                ];
            }

            $productShopData = [];
            if (null !== $price) {
                $productShopData = [
                    'price' => $price->getPrice()->getValue(),
                    'unit' => (string) $price->getUnit()->getValue()->value,
                ];
            }

            $ordersData[] = [
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
                    'image' => $order->getProduct()->getImage()->getValue(),
                    'created_on' => $order->getProduct()->getCreatedOn()->format('Y-m-d H:i:s'),
                ],
                'shop' => $shopData,
                'productShop' => $productShopData,
            ];
        }

        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'list_orders' => [
                'id' => $this->listOrders->getId()->getValue(),
                'group_id' => $this->listOrders->getGroupId()->getValue(),
                'user_id' => $this->listOrders->getUserId()->getValue(),
                'name' => $this->listOrders->getName()->getValue(),
                'description' => $this->listOrders->getDescription()->getValue(),
                'date_to_buy' => $this->listOrders->getDateToBuy()->getValue()?->format('Y-m-d H:i:s'),
                'created_on' => $this->listOrders->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            'orders' => $ordersData,
        ];
    }
}

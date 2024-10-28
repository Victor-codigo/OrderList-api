<?php

declare(strict_types=1);

namespace Test\Functional\Share\Adapter\Http\Controller\ShareListOrdersGetData;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use PHPUnit\Framework\Attributes\Test;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Shop\Domain\Model\Shop;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShareListOrdersGetDataControllerTest extends WebClientTestCase
{
    private const string METHOD = 'GET';
    private const string ENDPOINT = '/api/v1/share/list-orders';
    private const string SHARE_ID = '72b37f9c-ff55-4581-a131-4270e73012a2';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param array{
     *  list_orders: object{
     *      id: string|null,
     *      group_id: string|null,
     *      user_id: string|null,
     *      name: string|null,
     *      description: string|null,
     *      date_to_buy: string|null
     *  },
     *  orders: array<int, object{
     *      id: string|null,
     *      group_id: string|null,
     *      list_orders_id: string|null,
     *      user_id: string|null,
     *      description: string|null,
     *      amount: float|null,
     *      bought: bool,
     *      created_on: string,
     *      product: object{
     *          id: string|null,
     *          name: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      },
     *      shop: null|object{
     *          id: string|null,
     *          name: string|null,
     *          address: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      },
     *      product_shop: null|object{
     *          price: float|null,
     *          unit: string|null
     *      }
     *  }>
     * } $listOrdersData
     * @param array{
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
     *      product_shop: array{}|array{
     *          price: float|null,
     *          unit: UNIT_MEASURE_TYPE|null
     *      }
     *  }>
     * } $listOrdersDataExpected
     */
    private function assertListOrdersDataIsOk(array $listOrdersData, array $listOrdersDataExpected): void
    {
        $listOrders = $listOrdersData['list_orders'];
        $listOrdersExpected = $listOrdersDataExpected['list_orders'];
        $this->assertEquals($listOrdersExpected['id'], $listOrders->id);
        $this->assertEquals($listOrdersExpected['group_id'], $listOrders->group_id);
        $this->assertEquals($listOrdersExpected['user_id'], $listOrders->user_id);
        $this->assertEquals($listOrdersExpected['name'], $listOrders->name);
        $this->assertEquals($listOrdersExpected['description'], $listOrders->description);
        $this->assertEquals($listOrdersExpected['date_to_buy'], $listOrders->date_to_buy);

        $orders = $listOrdersData['orders'];
        usort($orders, $this->sortOrdersById(...));
        $ordersExpected = $listOrdersDataExpected['orders'];
        usort($ordersExpected, $this->sortOrdersById(...));
        $this->assertEquals(count($ordersExpected), count($orders));

        foreach ($orders as $key => $order) {
            $this->assertEquals($ordersExpected[$key]['id'], $order->id);
            $this->assertEquals($ordersExpected[$key]['group_id'], $order->group_id);
            $this->assertEquals($ordersExpected[$key]['list_orders_id'], $order->list_orders_id);
            $this->assertEquals($ordersExpected[$key]['user_id'], $order->user_id);
            $this->assertEquals($ordersExpected[$key]['description'], $order->description);
            $this->assertEquals($ordersExpected[$key]['amount'], $order->amount);
            $this->assertEquals($ordersExpected[$key]['bought'], $order->bought);
            $this->assertEquals($ordersExpected[$key]['created_on'], $order->created_on);

            $product = $order->product;
            $productExpected = $ordersExpected[$key]['product'];
            $productImageUrl = 'http://nginx-api/assets/img/products/test/';
            $this->assertEquals($productExpected['id'], $product->id);
            $this->assertEquals($productExpected['name'], $product->name);
            $this->assertEquals($productExpected['description'], $product->description);
            $this->assertEquals(null === $productExpected['image'] ? null : $productImageUrl.$productExpected['image'], $product->image);
            $this->assertEquals($productExpected['created_on'], $product->created_on);

            $this->assertOrderShopIsOk($ordersExpected[$key]['shop'], $order->shop);
            $this->assertOrderProductShopIsOk($ordersExpected[$key]['product_shop'], $order->product_shop);
        }
    }

    private function sortOrdersById(mixed $order1, mixed $order2): int
    {
        $order1Aux = (array) $order1;
        $order2Aux = (array) $order2;

        return $order1Aux['id'] <=> $order2Aux['id'];
    }

    /**
     * @param array{}|array{
     *          id: string|null,
     *          name: string|null,
     *          address: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      } $shopExpected
     * @param array{}|object{
     *          id: string|null,
     *          name: string|null,
     *          address: string|null,
     *          description: string|null,
     *          image: string|null,
     *          created_on: string
     *      } $shop
     */
    private function assertOrderShopIsOk(array $shopExpected, array|object $shop): void
    {
        $shopImageUrl = 'http://nginx-api/assets/img/shops/test/';

        if (empty($shopExpected)) {
            $this->assertEmpty($shop);
        }

        $this->assertEquals($shopExpected['id'], $shop->id);
        $this->assertEquals($shopExpected['name'], $shop->name);
        $this->assertEquals($shopExpected['address'], $shop->address);
        $this->assertEquals($shopExpected['description'], $shop->description);
        $this->assertEquals(null === $shopExpected['image'] ? null : $shopImageUrl.$shopExpected['image'], $shop->image);
        $this->assertEquals($shopExpected['created_on'], $shop->created_on);
    }

    /**
     * @param array{}|array{
     *   price: float|null,
     *   unit: UNIT_MEASURE_TYPE|null
     * } $productShopExpected
     * @param array{}|object{
     *   price: float|null,
     *   unit: string|null
     * } $productShop
     */
    private function assertOrderProductShopIsOk(array $productShopExpected, array|object $productShop): void
    {
        if (empty($productShopExpected)) {
            $this->assertEmpty($productShop);

            return;
        }

        $this->assertEquals($productShopExpected['price'], $productShop->price);
        $this->assertEquals($productShopExpected['unit']->value, $productShop->unit);
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
     *      product_shop: array{}|array{
     *          price: float|null,
     *          unit: UNIT_MEASURE_TYPE|null
     *      }
     *  }>
     * }
     */
    private function getListOrdersDataExpected(): array
    {
        $entityManager = $this->getEntityManager();
        /** @var ListOrders[] $listOrders */
        $listOrders = $entityManager->getRepository(ListOrders::class)->findBy([
            'id' => ValueObjectFactory::createIdentifier('ba6bed75-4c6e-4ac3-8787-5bded95dac8d'),
        ]);
        /** @var Order[] $orders */
        $orders = $entityManager->getRepository(Order::class)->findBy([
            'listOrders' => ValueObjectFactory::createIdentifier('ba6bed75-4c6e-4ac3-8787-5bded95dac8d')]
        );
        $productsId = array_map(
            fn (Order $order): Identifier => $order->getProductId(),
            $orders
        );
        $shopsId = array_map(
            fn (Order $order): Identifier => $order->getShopId(),
            $orders
        );

        /** @var Product[] $products */
        $products = $entityManager->getRepository(Product::class)->findBy(['id' => $productsId]);
        /** @var Shop[] $shops */
        $shops = $entityManager->getRepository(Shop::class)->findBy(['id' => $shopsId]);
        /** @var ProductShop[] $productsShops */
        $productsShops = $entityManager->getRepository(ProductShop::class)->findBy([
            'productId' => $productsId,
            'shopId' => $shopsId,
        ]);

        return [
            'list_orders' => $this->getListOrdersData($listOrders[0]),
            'orders' => $this->getOrdersData($orders, $products, $shops, $productsShops),
        ];
    }

    /**
     * @return array{
     *      id: string|null,
     *      group_id: string|null,
     *      user_id: string|null,
     *      name: string|null,
     *      description: string|null,
     *      date_to_buy: string|null
     *  }
     */
    private function getListOrdersData(ListOrders $listOrders): array
    {
        return [
            'id' => $listOrders->getId()->getValue(),
            'group_id' => $listOrders->getGroupId()->getValue(),
            'user_id' => $listOrders->getUserId()->getValue(),
            'name' => $listOrders->getName()->getValue(),
            'description' => $listOrders->getDescription()->getValue(),
            'date_to_buy' => $listOrders->getDateToBuy()->getValue()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Order[]       $orders
     * @param Product[]     $products
     * @param Shop[]        $shops
     * @param ProductShop[] $productsShops
     *
     * @return array<int, array{
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
     *      product_shop: array{}|array{
     *          price: float|null,
     *          unit: UNIT_MEASURE_TYPE|null
     *      }
     *  }>
     */
    private function getOrdersData(array $orders, array $products, array $shops, array $productsShops): array
    {
        $ordersData = [];
        foreach ($orders as $order) {
            $orderProducts = array_filter(
                $products,
                fn (Product $product): bool => $product->getId()->equalTo($order->getProductId())
            );
            $orderProducts = reset($orderProducts);
            $orderShops = array_filter(
                $shops,
                fn (Shop $shop): bool => $shop->getId()->equalTo($order->getShopId())
            );
            $orderShops = reset($orderShops);
            $orderProductsShops = array_filter(
                $productsShops,
                fn (ProductShop $productShop): bool => $productShop->getProductId()->equalTo($order->getProductId())
                                                    && $productShop->getShopId()->equalTo($order->getShopId())
            );
            $orderProductsShops = reset($orderProductsShops);

            $shopData = [];
            if (!empty($orderShops)) {
                $shopData = [
                    'id' => $orderShops->getId()->getValue(),
                    'name' => $orderShops->getName()->getValue(),
                    'address' => $orderShops->getAddress()->getValue(),
                    'description' => $orderShops->getDescription()->getValue(),
                    'image' => $orderShops->getImage()->getValue(),
                    'created_on' => $orderShops->getCreatedOn()->format('Y-m-d H:i:s'),
                ];
            }

            $productShopData = [];
            if (!empty($orderProductsShops)) {
                $productShopData = [
                    'price' => $orderProductsShops->getPrice()->getValue(),
                    'unit' => $orderProductsShops->getUnit()->getValue(),
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
                    'id' => $orderProducts->getId()->getValue(),
                    'name' => $orderProducts->getName()->getValue(),
                    'description' => $orderProducts->getDescription()->getValue(),
                    'image' => $orderProducts->getImage()->getValue(),
                    'created_on' => $orderProducts->getCreatedOn()->format('Y-m-d H:i:s'),
                ],
                'shop' => $shopData,
                'product_shop' => $productShopData,
            ];
        }

        return $ordersData;
    }

    #[Test]
    public function itShouldGetListOrdersData(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $page = 1;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $listOrdersDataExpected = $this->getListOrdersDataExpected();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['list_orders', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders shared data', $responseContent->message);

        $this->assertListOrdersDataIsOk((array) $responseContent->data, $listOrdersDataExpected);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataRecourseListOrdersNotFound(): void
    {
        $recourseId = '6521303a-5657-46e4-881c-8b929543b18f';
        $page = 1;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$recourseId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);

        $this->assertEquals('List orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataListOrdersIsNull(): void
    {
        $page = 1;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shared_list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shared_list_orders_id);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataListOrdersIsWrong(): void
    {
        $page = 1;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?shared_list_orders_id=Wrong id'
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shared_list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->shared_list_orders_id);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataPageIsNull(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataPageIsWrong(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $page = -1;
        $pageItems = 10;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}"
                .'&page={$page}'
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataPageItemsIsNull(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $page = 1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}"
                ."&page={$page}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataPageItemsIsWrong(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $page = 1;
        $pageItems = -1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }
}

<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrdersGroupGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Shop\Domain\Model\Shop;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrdersGroupGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/orders/group';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_ORDERS_NOT_BELONGS = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';

    private function assertOrderIsOk(Order $orderExpected, array $orderActual): void
    {
        $this->assertArrayHasKey('id', $orderActual);
        $this->assertArrayHasKey('user_id', $orderActual);
        $this->assertArrayHasKey('group_id', $orderActual);
        $this->assertArrayHasKey('description', $orderActual);
        $this->assertArrayHasKey('amount', $orderActual);
        $this->assertArrayHasKey('created_on', $orderActual);
        $this->assertArrayHasKey('product', $orderActual);
        $this->assertArrayHasKey('shop', $orderActual);

        $this->assertArrayHasKey('id', $orderActual['product']);
        $this->assertArrayHasKey('name', $orderActual['product']);
        $this->assertArrayHasKey('description', $orderActual['product']);
        $this->assertArrayHasKey('image', $orderActual['product']);
        $this->assertArrayHasKey('created_on', $orderActual['product']);

        if (!$orderExpected->getShopId()->isNull()) {
            $this->assertArrayHasKey('id', $orderActual['shop']);
            $this->assertArrayHasKey('name', $orderActual['shop']);
            $this->assertArrayHasKey('description', $orderActual['shop']);
            $this->assertArrayHasKey('image', $orderActual['shop']);
            $this->assertArrayHasKey('created_on', $orderActual['shop']);

            $this->assertArrayHasKey('price', $orderActual['productShop']);
            $this->assertArrayHasKey('unit', $orderActual['productShop']);
        }

        $this->assertEquals($orderExpected->getId()->getValue(), $orderActual['id']);
        $this->assertEquals($orderExpected->getUserId()->getValue(), $orderActual['user_id']);
        $this->assertEquals($orderExpected->getGroupId()->getValue(), $orderActual['group_id']);
        $this->assertEquals($orderExpected->getDescription()->getValue(), $orderActual['description']);
        $this->assertEquals($orderExpected->getAmount()->getvalue(), $orderActual['amount']);

        $product = $orderExpected->getProduct();
        $this->assertEquals($product->getId()->getvalue(), $orderActual['product']['id']);
        $this->assertEquals($product->getName()->getValue(), $orderActual['product']['name']);
        $this->assertEquals($product->getDescription()->getValue(), $orderActual['product']['description']);
        $this->assertEquals($product->getImage()->getValue(), $orderActual['product']['image']);

        if (!$orderExpected->getShopId()->isNull()) {
            $shop = $orderExpected->getShop();
            $this->assertEquals($shop->getId()->getValue(), $orderActual['shop']['id']);
            $this->assertEquals($shop->getName(), $orderActual['shop']['name']);
            $this->assertEquals($shop->getDescription()->getValue(), $orderActual['shop']['description']);
            $this->assertEquals($shop->getImage()->getValue(), $orderActual['shop']['image']);

            /** @var ProductShop[] $productShop */
            $productShop = $orderExpected->getProduct()->getProductShop()->getValues();
            $this->assertEquals($productShop[0]->getPrice()->getValue(), $orderActual['productShop']['price']);
            $this->assertEquals($productShop[0]->getUnit()->getValue()->value, $orderActual['productShop']['unit']);
        }
    }

    private function getOrdersData(): array
    {
        $listOrders1 = ListOrders::fromPrimitives(
            'd446eab9-5199-48d0-91f5-0407a86bcb4f',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'List order name 2',
            'List order description 2',
            new \DateTime('2023-05-28 10:20:15')
        );

        $listOrders2 = ListOrders::fromPrimitives(
            'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'List order name 1',
            'List order description 1',
            new \DateTime('2023-05-29 10:20:15')
        );

        $listOrders3 = ListOrders::fromPrimitives(
            'f2980f67-4eb9-41ca-b452-ffa2c7da6a37',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'List order name 3',
            'List order description 3',
            new \DateTime('2023-05-27 10:20:15')
        );

        $product1 = Product::fromPrimitives(
            '8b6d650b-7bb7-4850-bf25-36cda9bce801',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Juan Carlos',
            null,
            null
        );
        $product2 = Product::fromPrimitives(
            'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Maluela',
            'Product description 1',
            null
        );
        $product3 = Product::fromPrimitives(
            '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Juanola',
            'Product description 1',
            null
        );
        $product4 = Product::fromPrimitives(
            'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Perico',
            'Product description 1',
            null
        );

        $shop1 = Shop::fromPrimitives(
            'e6c1d350-f010-403c-a2d4-3865c14630ec',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop name 1',
            'Shop description 1',
            null
        );
        $shop2 = Shop::fromPrimitives(
            'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop name 2',
            'Shop description 2',
            null
        );
        $shop3 = Shop::fromPrimitives(
            'b9b1c541-d41e-4751-9ecb-4a1d823c0405',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop name 3',
            null,
            null
        );

        $product1->setProductShop([ProductShop::fromPrimitives($product1, $shop2, 20.3, UNIT_MEASURE_TYPE::UNITS)]);
        $product2->setProductShop([ProductShop::fromPrimitives($product2, $shop1, 10.5, UNIT_MEASURE_TYPE::UNITS)]);
        $product3->setProductShop([ProductShop::fromPrimitives($product3, $shop2, null, UNIT_MEASURE_TYPE::UNITS)]);
        $product4->setProductShop([ProductShop::fromPrimitives($product4, $shop3, null, UNIT_MEASURE_TYPE::KG)]);

        return [
            'a0b4760a-9037-477a-8b84-d059ae5ee7e9' => Order::fromPrimitives(
                'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'order description 2',
                20.050,
                true,
                $listOrders1,
                $product1,
                $shop2
            ),
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8' => Order::fromPrimitives(
                '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'order description',
                10.200,
                false,
                $listOrders2,
                $product2,
                $shop1
            ),
            '5cfe52e5-db78-41b3-9acd-c3c84924cb9b' => Order::fromPrimitives(
                '5cfe52e5-db78-41b3-9acd-c3c84924cb9b',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                null,
                20.050,
                true,
                $listOrders2,
                $product1,
                $shop2
            ),
            'c3734d1c-8b18-4bfd-95aa-06a261476d9d' => Order::fromPrimitives(
                'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '6df60afd-f7c3-4c2c-b920-e265f266c560',
                'order description 4',
                40.000,
                false,
                $listOrders2,
                $product3,
                $shop2
            ),
            'd351adba-c566-4fa5-bb5b-1a6f73b1d72f' => Order::fromPrimitives(
                'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '6df60afd-f7c3-4c2c-b920-e265f266c560',
                'order description 3',
                30.150,
                false,
                $listOrders3,
                $product4,
                $shop3
            ),
        ];
    }

    /** @test */
    public function itShouldGetOrdersGroupData(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'?page=1'
                .'&page_items=100'
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders of the group data', $responseContent->message);

        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(1, $responseContent->data->pages_total);
        $this->assertCount(5, $responseContent->data->orders);
        $ordersExpected = $this->getOrdersData();

        foreach ($responseContent->data->orders as $orderActual) {
            $orderActual = (array) $orderActual;
            $orderActual['product'] = (array) $orderActual['product'];
            $orderActual['shop'] = (array) $orderActual['shop'];
            $orderActual['productShop'] = (array) $orderActual['productShop'];
            $this->assertArrayHasKey($orderActual['id'], $ordersExpected);
            $this->assertOrderIsOk($ordersExpected[$orderActual['id']], $orderActual);
        }
    }

    /** @test */
    public function itShouldGetOneOrderGroupData(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'?page=1'
                .'&page_items=1'
                .'&order_asc=false'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders of the group data', $responseContent->message);

        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(5, $responseContent->data->pages_total);
        $this->assertCount(1, $responseContent->data->orders);
        $ordersExpected = $this->getOrdersData();

        $orderActual = (array) $responseContent->data->orders[0];
        $orderActual['product'] = (array) $orderActual['product'];
        $orderActual['shop'] = (array) $orderActual['shop'];
        $orderActual['productShop'] = (array) $orderActual['productShop'];
        $this->assertArrayHasKey($orderActual['id'], $ordersExpected);
        $this->assertOrderIsOk($ordersExpected[$orderActual['id']], $orderActual);
    }

    /** @test */
    public function itShouldFailGetOrdersGroupIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/wrong id'
                .'?page=1'
                .'&page_items=100'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGetOrdersPageIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'&page_items=100'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGetOrdersPageWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'?page=-1'
                .'&page_items=100'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGetOrdersPageItemsIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'&page=1'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGetOrdersPageItemsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'?page=1'
                .'&page_items=0'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGetOrdersNoOrdersFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID_ORDERS_NOT_BELONGS
                .'?page=1'
                .'&page_items=100'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Cannot find orders in the group', $responseContent->message);
    }

    /** @test */
    public function itShouldFailUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'/'.self::GROUP_ID
                .'?page=1'
                .'&page_items=100'
                .'order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);

        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

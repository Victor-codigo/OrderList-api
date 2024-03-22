<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/orders';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        '5cfe52e5-db78-41b3-9acd-c3c84924cb9b',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
    ];

    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_EXISTS_USER_NOT_BELONGS = '4d52266f-aa7e-324e-b92d-6152635dd09e';

    private function getOrdersDataExpected(): array
    {
        return [
            self::ORDERS_ID[0] => [
                'id' => self::ORDERS_ID[0],
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                'product_id' => 'afc62bc9-c42c-4c4d-8098-09ce51414a92',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'description' => 'order description',
                'amount' => 10.200,
                'bought' => false,
                'created_on' => '2023-05-29 13:35:10',
                'price' => 10.50,
                'unit' => UNIT_MEASURE_TYPE::UNITS->value,
            ],
            self::ORDERS_ID[1] => [
                'id' => self::ORDERS_ID[1],
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'list_orders_id' => 'd446eab9-5199-48d0-91f5-0407a86bcb4f',
                'product_id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'shop_id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'description' => 'order description 2',
                'amount' => 20.050,
                'bought' => true,
                'created_on' => '2023-05-29 13:35:10',
                'price' => 20.30,
                'unit' => UNIT_MEASURE_TYPE::UNITS->value,
            ],
            self::ORDERS_ID[2] => [
                'id' => self::ORDERS_ID[2],
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                'product_id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'shop_id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'description' => null,
                'amount' => 20.050,
                'bought' => true,
                'created_on' => '2024-03-19 10:12:56',
                'price' => 20.30,
                'unit' => UNIT_MEASURE_TYPE::UNITS->value,
            ],
            self::ORDERS_ID[3] => [
                'id' => self::ORDERS_ID[3],
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                'product_id' => '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
                'shop_id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'user_id' => '6df60afd-f7c3-4c2c-b920-e265f266c560',
                'description' => 'order description 4',
                'amount' => 40,
                'bought' => false,
                'created_on' => '2024-03-19 10:12:56',
                'price' => null,
                'unit' => UNIT_MEASURE_TYPE::UNITS->value,
            ],
            self::ORDERS_ID[4] => [
                'id' => self::ORDERS_ID[4],
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'list_orders_id' => 'f2980f67-4eb9-41ca-b452-ffa2c7da6a37',
                'product_id' => 'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
                'shop_id' => 'b9b1c541-d41e-4751-9ecb-4a1d823c0405',
                'user_id' => '6df60afd-f7c3-4c2c-b920-e265f266c560',
                'description' => 'order description 3',
                'amount' => 30.150,
                'bought' => false,
                'created_on' => '2024-03-19 10:12:56',
                'price' => null,
                'unit' => UNIT_MEASURE_TYPE::KG->value,
            ],
        ];
    }

    private function assertOrderDataIsOk(array $orderExpected, object $orderDataActual): void
    {
        $this->assertTrue(property_exists($orderDataActual, 'id'));
        $this->assertTrue(property_exists($orderDataActual, 'group_id'));
        $this->assertTrue(property_exists($orderDataActual, 'list_orders_id'));
        $this->assertTrue(property_exists($orderDataActual, 'product_id'));
        $this->assertTrue(property_exists($orderDataActual, 'shop_id'));
        $this->assertTrue(property_exists($orderDataActual, 'user_id'));
        $this->assertTrue(property_exists($orderDataActual, 'description'));
        $this->assertTrue(property_exists($orderDataActual, 'amount'));
        $this->assertTrue(property_exists($orderDataActual, 'bought'));
        $this->assertTrue(property_exists($orderDataActual, 'created_on'));
        $this->assertTrue(property_exists($orderDataActual, 'price'));
        $this->assertTrue(property_exists($orderDataActual, 'unit'));

        $this->assertEquals($orderExpected['id'], $orderDataActual->id);
        $this->assertEquals($orderExpected['group_id'], $orderDataActual->group_id);
        $this->assertEquals($orderExpected['list_orders_id'], $orderDataActual->list_orders_id);
        $this->assertEquals($orderExpected['product_id'], $orderDataActual->product_id);
        $this->assertEquals($orderExpected['shop_id'], $orderDataActual->shop_id);
        $this->assertEquals($orderExpected['user_id'], $orderDataActual->user_id);
        $this->assertEquals($orderExpected['description'], $orderDataActual->description);
        $this->assertEquals($orderExpected['amount'], $orderDataActual->amount);
        $this->assertEquals($orderExpected['bought'], $orderDataActual->bought);
        $this->assertIsString($orderDataActual->created_on);
        $this->assertEquals($orderExpected['price'], $orderDataActual->price);
        $this->assertEquals($orderExpected['unit'], $orderDataActual->unit);
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupId(): void
    {
        $groupId = self::GROUP_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertCount(count(self::ORDERS_ID), $responseContent->data->orders);

        foreach ($responseContent->data->orders as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdAndOrdersId(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $groupId = self::GROUP_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = false;
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&orders_id={$ordersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertCount(count(self::ORDERS_ID), $responseContent->data->orders);

        foreach ($responseContent->data->orders as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdAndListOrderName(): void
    {
        $groupId = self::GROUP_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::LIST_ORDERS->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'List order name 1';
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertCount(3, $responseContent->data->orders);

        $ordersIdExpected = [
            self::ORDERS_ID[0],
            self::ORDERS_ID[2],
            self::ORDERS_ID[3],
        ];

        foreach ($responseContent->data->orders as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertContains($orderData->id, $ordersIdExpected);
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdAndProductName(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::PRODUCT->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertCount(1, $responseContent->data->orders);

        $ordersIdExpected = [
            self::ORDERS_ID[2],
        ];

        foreach ($responseContent->data->orders as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertContains($orderData->id, $ordersIdExpected);
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdAndShopName(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::SHOP->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Shop name 2';
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertCount(2, $responseContent->data->orders);

        $ordersIdExpected = [
            self::ORDERS_ID[2],
            self::ORDERS_ID[3],
        ];

        foreach ($responseContent->data->orders as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertContains($orderData->id, $ordersIdExpected);
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersDataOrdersNotFound(): void
    {
        $groupId = self::GROUP_ID;
        $orders_id = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&orders_id={$orders_id}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingOrdersDataGroupIdIsNull(): void
    {
        $groupId = null;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataGroupIdIsWrong(): void
    {
        $groupId = 'wrong id';
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataListOrdersIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = 'wrong id';
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataOrdersIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $ordersId = implode(',', array_merge(self::ORDERS_ID, ['wrong id']));
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&orders_id={$ordersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->orders_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataPageIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = 'wrong id';
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataPageIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = 'wrong id';
        $page = -1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataPageItemsIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataPageItemsIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = -1;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataFiltersSectionIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = 'wrong filter';
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['section_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->section_filter_type);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataFiltersTextIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterText = 'wrong filter';
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['text_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->text_filter_type);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataFilterTextMissing(): void
    {
        $groupId = self::GROUP_ID;
        $listOrdersId = self::LIST_ORDERS_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $filterSection = FILTER_SECTION::ORDER->value;
        $filterValue = 'Juan Carlos';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&list_orders_id={$listOrdersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
                ."&filter_section={$filterSection}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_null'], $responseContent->errors->filter_section_and_text_not_empty);
    }

    /** @test */
    public function itShouldFailGetOrdersDataUserNotBelongsToTheGroup(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $groupId = self::GROUP_ID;
        $page = 1;
        $pageItems = 10;
        $orderAsc = true;
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&orders_id={$ordersId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderCreate;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderCreateControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/orders';
    private const string METHOD = 'POST';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';

    private string $pathImageProduct;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function getOrdersData(): array
    {
        return [
            [
                'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                'product_id' => 'afc62bc9-c42c-4c4d-8098-09ce51414a92',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'description' => 'order 1 description',
                'amount' => 10.56,
            ],
            [
                'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                'product_id' => '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'description' => 'order 2 description',
                'amount' => 20.56,
            ],
            [
                'list_orders_id' => 'd446eab9-5199-48d0-91f5-0407a86bcb4f',
                'product_id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'shop_id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'description' => 'order 3 description',
                'amount' => 30.56,
            ],
        ];
    }

    private function getOrdersDataNew(): array
    {
        return [
            [
                'product_id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'description' => 'order 1 description',
                'amount' => 10.56,
            ],
            [
                'product_id' => 'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'description' => 'order 2 description',
                'amount' => 20.56,
            ],
        ];
    }

    #[Test]
    public function itShouldCreateOrders(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders created', $responseContent->message);
        $this->assertIsArray($responseContent->data->id);
        $this->assertCount(count($ordersData), $responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateOrdersDescriptionAndAmountAreNull(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['description'] = null;
        $ordersData[0]['amount'] = null;
        $ordersData[1]['description'] = null;
        $ordersData[1]['amount'] = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders created', $responseContent->message);
        $this->assertIsArray($responseContent->data->id);
        $this->assertCount(count($ordersData), $responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateOrdersShopIdIsNull(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['shop_id'] = null;
        $ordersData[1]['shop_id'] = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders created', $responseContent->message);
        $this->assertIsArray($responseContent->data->id);
        $this->assertCount(count($ordersData), $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailCreatingOrdersGroupIdIsNull(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    #[Test]
    public function itShouldFailCreatingOrdersGroupIdIsWrong(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'wrong id',
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    #[Test]
    public function itShouldFailCreatingOrdersIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->orders_empty);
    }

    #[Test]
    public function itShouldFailCreatingOrdersListsOrdersIdIsNull(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->list_orders_id);
    }

    #[Test]
    public function itShouldFailCreatingOrdersListsOrdersIdINotFound(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387',
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List of orders or lists of orders not found', $responseContent->message);
        $this->assertEquals('List of orders or lists of orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailCreatingOrdersProductIdIsNull(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['product_id'] = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [0], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors[0]->product_id);
    }

    #[Test]
    public function itShouldFailCreatingOrdersProductIdINotFound(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['product_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product or products not found', $responseContent->message);
        $this->assertEquals('Product or products not found', $responseContent->errors->product_not_found);
    }

    #[Test]
    public function itShouldFailCreatingOrdersNoneProductIdIFound(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['product_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $ordersData[1]['product_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product or products not found', $responseContent->message);
        $this->assertEquals('Product or products not found', $responseContent->errors->product_not_found);
    }

    #[Test]
    public function itShouldFailCreatingOrdersShopIdINotFound(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['shop_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop or shops not found', $responseContent->message);
        $this->assertEquals('Shop or shops not found', $responseContent->errors->shop_not_found);
    }

    #[Test]
    public function itShouldFailCreatingOrdersNoneShopIdIFound(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $ordersData[0]['shop_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $ordersData[1]['shop_id'] = '96ca5611-1b9d-4c21-ba2f-0fe4c8a95387';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop or shops not found', $responseContent->message);
        $this->assertEquals('Shop or shops not found', $responseContent->errors->shop_not_found);
    }

    #[Test]
    public function itShouldFailCreatingOrdersProductAndShopRepeated(): void
    {
        $ordersData = $this->getOrdersData();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_product_and_shop_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product and shop are already in the order list', $responseContent->message);
        $this->assertEquals('Product and shop are already in the order list', $responseContent->errors->order_product_and_shop_repeated);
    }

    #[Test]
    public function itShouldFailCreatingOrdersUserNotBelongsToTheGroup(): void
    {
        $ordersData = $this->getOrdersDataNew();
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'orders_data' => $ordersData,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

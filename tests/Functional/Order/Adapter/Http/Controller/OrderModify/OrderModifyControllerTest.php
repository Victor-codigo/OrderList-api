<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderModify;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/orders';
    private const string METHOD = 'PUT';
    private const string ORDER_ID = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const string LIST_ORDERS_ID = 'd446eab9-5199-48d0-91f5-0407a86bcb4f';
    private const string LIST_ORDERS_ID_2 = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const string PRODUCT_NOT_IN_A_SHOP_ID = 'ca10c90a-c7e6-4594-89e9-71d2f5e74710';
    private const string SHOP_ID = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';

    #[Test]
    public function itShouldModifyOrder(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => 100.36,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Order modified', $responseContent->message);
        $this->assertEquals(self::ORDER_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyOrderShopIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => null,
                'description' => 'order description modified',
                'amount' => 100.36,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Order modified', $responseContent->message);
        $this->assertEquals(self::ORDER_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyOrderDescriptionIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => null,
                'amount' => 100.36,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Order modified', $responseContent->message);
        $this->assertEquals(self::ORDER_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyOrderAmountIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Order modified', $responseContent->message);
        $this->assertEquals(self::ORDER_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailModifyingOrderOrderIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => null,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->order_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderOrderIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => 'wrong id',
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->order_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
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
    public function itShouldFailModifyingOrderGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'wrong id',
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
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
    public function itShouldFailModifyingOrderListOrdersIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => null,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
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
    public function itShouldFailModifyingOrderListOrdersIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => 'wrong id',
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderListOrdersIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => 'ceaaac38-19ed-4aee-9d80-7a19e3da5dba',
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);
        $this->assertEquals('List orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailModifyingOrderProductIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => null,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->product_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderProductIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => 'wrong id',
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->product_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderShopIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => 'wrong id',
                'description' => 'order description modified',
                'amount' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->shop_id);
    }

    #[Test]
    public function itShouldFailModifyingOrderDescriptionIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => str_pad('', 501, 'p'),
                'amount' => 15,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['description'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->description);
    }

    #[Test]
    public function itShouldFailModifyingOrderAmountIsNegative(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => -1,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['amount'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['positive_or_zero'], $responseContent->errors->amount);
    }

    #[Test]
    public function itShouldFailModifyingOrderOrderNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => 'a53cf32e-815c-4540-b0ac-674b55de96bb',
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Order not found', $responseContent->message);
        $this->assertEquals('Order not found', $responseContent->errors->order_not_found);
    }

    #[Test]
    public function itShouldFailModifyingOrderProductNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => 'a53cf32e-815c-4540-b0ac-674b55de96bb',
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);
        $this->assertEquals('Product not found', $responseContent->errors->product_not_found);
    }

    #[Test]
    public function itShouldFailModifyingOrderShopNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => 'a53cf32e-815c-4540-b0ac-674b55de96bb',
                'description' => 'order description modify',
                'amount' => 0,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop not found, or product is not in the shop', $responseContent->message);
        $this->assertEquals('Shop not found, or product is not in the shop', $responseContent->errors->shop_not_found);
    }

    #[Test]
    public function itShouldFailModifyingOrderProductNotInAShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_NOT_IN_A_SHOP_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop not found, or product is not in the shop', $responseContent->message);
        $this->assertEquals('Shop not found, or product is not in the shop', $responseContent->errors->shop_not_found);
    }

    #[Test]
    public function itShouldFailModifyingOrderProductAndShopRepeatedInListOrders(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID_2,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
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
    public function itShouldFailModifyingOrderUserDoesNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'list_orders_id' => self::LIST_ORDERS_ID,
                'order_id' => self::ORDER_ID,
                'product_id' => self::PRODUCT_NOT_IN_A_SHOP_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
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

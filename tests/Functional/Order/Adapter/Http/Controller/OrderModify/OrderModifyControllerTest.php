<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderModify;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/orders';
    private const METHOD = 'PUT';
    private const ORDER_ID = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const PRODUCT_NOT_IN_A_SHOP_ID = 'ca10c90a-c7e6-4594-89e9-71d2f5e74710';
    private const SHOP_ID = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';

    /** @test */
    public function itShouldModifyOrder(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldModifyOrderShopIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldModifyOrderDescriptionIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldModifyOrderAmountIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderOrderIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => null,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderOrderIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => 'wrong id',
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => null,
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

    /** @test */
    public function itShouldFailModifyingOrderGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => 'wrong id',
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

    /** @test */
    public function itShouldFailModifyingOrderProductIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderProductIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderShopIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderDescriptionIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderAmountIsNegative(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderOrderNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => 'a53cf32e-815c-4540-b0ac-674b55de96bb',
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderProductNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderShopNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderProductNotInAShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
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

    /** @test */
    public function itShouldFailModifyingOrderUserDoesNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_NOT_IN_A_SHOP_ID,
                'shop_id' => self::SHOP_ID,
                'description' => 'order description modify',
                'amount' => 0,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_error'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->group_error);
    }
}

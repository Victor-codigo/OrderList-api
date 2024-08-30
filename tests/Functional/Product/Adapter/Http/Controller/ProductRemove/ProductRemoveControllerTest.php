<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductRemove;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/products';
    private const string METHOD = 'DELETE';
    private const string USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const string USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string PRODUCT_EXISTS_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const string PRODUCT_EXISTS_2_ID = '8b6d650b-7bb7-4850-bf25-36cda9bce801';
    private const string SHOP_EXISTS_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';

    #[Test]
    public function itShouldRemoveAProduct(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => [self::SHOP_EXISTS_ID],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product removed', $responseContent->message);
        $this->assertEquals([self::PRODUCT_EXISTS_ID], $responseContent->data->id);
    }

    #[Test]
    public function itShouldRemoveManyProductWithoutShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [
                    self::PRODUCT_EXISTS_ID,
                    self::PRODUCT_EXISTS_2_ID,
                ],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product removed', $responseContent->message);
        $this->assertEqualsCanonicalizing([self::PRODUCT_EXISTS_ID, self::PRODUCT_EXISTS_2_ID], $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailRemovingAProductGroupIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => [self::SHOP_EXISTS_ID],
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
    public function itShouldFailRemovingAProductGroupIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'group id wrong',
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => [self::SHOP_EXISTS_ID],
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
    public function itShouldFailRemovingAProductGroupNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'group not exists',
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => [self::SHOP_EXISTS_ID],
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
    public function itShouldFailRemovingAProductProductIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => null,
                'shops_id' => [self::SHOP_EXISTS_ID],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->products_id_empty);
    }

    #[Test]
    public function itShouldFailRemovingAProductProductIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => ['product not exists'],
                'shops_id' => [self::SHOP_EXISTS_ID],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->products_id);
    }

    #[Test]
    public function itShouldFailRemovingAProductNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => ['fc4f5962-02d1-4d80-b3ae-8aeffbcb6268'],
                'shops_id' => [self::SHOP_EXISTS_ID],
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
    public function itShouldFailRemovingAProductShopIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => ['shop not exists'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->shops_id);
    }

    #[Test]
    public function itShouldFailRemovingAProductShopNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => ['ff56889f-e8d7-4e80-88e0-6c3b5a4eff0d'],
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
    public function itShouldFailRemovingAProductShopNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => ['fc4f5962-02d1-4d80-b3ae-8aeffbcb6268'],
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
    public function itShouldFailRemovingAProductUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_HAS_NO_GROUP_EMAIL, self::USER_HAS_NO_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'products_id' => [self::PRODUCT_EXISTS_ID],
                'shops_id' => [self::SHOP_EXISTS_ID],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);
        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }
}

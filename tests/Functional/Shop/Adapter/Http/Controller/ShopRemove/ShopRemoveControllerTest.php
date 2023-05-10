<?php

declare(strict_types=1);

namespace Test\Functional\Shop\Adapter\Http\Controller\ShopRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShopRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/shops';
    private const METHOD = 'DELETE';
    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const SHOP_EXISTS_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const PRODUCT_EXISTS_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';

    /** @test */
    public function itShouldRemoveAShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => self::PRODUCT_EXISTS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shop removed', $responseContent->message);
        $this->assertEquals(self::SHOP_EXISTS_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailRemovingAShopGroupIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => self::PRODUCT_EXISTS_ID,
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
    public function itShouldFailRemovingAShopGroupNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'group not exists',
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => self::PRODUCT_EXISTS_ID,
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
    public function itShouldFailRemovingAShopShopIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => null,
                'product_id' => self::PRODUCT_EXISTS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shop_id);
    }

    /** @test */
    public function itShouldFailRemovingAShopShopNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => 'shop not exists',
                'product_id' => self::PRODUCT_EXISTS_ID,
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
    public function itShouldFailRemovingAShopProductIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => null,
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
    public function itShouldFailRemovingAShopProductNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => 'shop not exists',
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
    public function itShouldFailRemovingAShopNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => 'fc4f5962-02d1-4d80-b3ae-8aeffbcb6268',
                'product_id' => self::PRODUCT_EXISTS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop not found', $responseContent->message);
        $this->assertEquals('Shop not found', $responseContent->errors->shop_not_found);
    }

    /** @test */
    public function itShouldFailRemovingAShopGroupNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'fc4f5962-02d1-4d80-b3ae-8aeffbcb6268',
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => self::PRODUCT_EXISTS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);
        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailRemovingAShopProductNotFound22(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => 'fc4f5962-02d1-4d80-b3ae-8aeffbcb6268',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop not found', $responseContent->message);
        $this->assertEquals('Shop not found', $responseContent->errors->shop_not_found);
    }

    /** @test */
    public function itShouldFailRemovingAShopUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_HAS_NO_GROUP_EMAIL, self::USER_HAS_NO_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shop_id' => self::SHOP_EXISTS_ID,
                'product_id' => self::PRODUCT_EXISTS_ID,
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

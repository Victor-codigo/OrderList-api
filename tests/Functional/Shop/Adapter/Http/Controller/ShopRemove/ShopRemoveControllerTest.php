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

    private const string ENDPOINT = '/api/v1/shops';
    private const string METHOD = 'DELETE';
    private const string USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const string USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const array SHOP_EXISTS_ID = [
        'e6c1d350-f010-403c-a2d4-3865c14630ec',
        'cc7f5dd6-02ba-4bd9-b5c1-5b65d81e59a0',
        'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
    ];

    /** @test */
    public function itShouldRemoveShops(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => self::SHOP_EXISTS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops removed', $responseContent->message);

        $this->assertCount(3, $responseContent->data->id);
        $this->assertEqualsCanonicalizing(self::SHOP_EXISTS_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldRemoveShopsWithShopIdValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => array_merge(
                    self::SHOP_EXISTS_ID,
                    [
                        '22742ac9-8569-4dc8-9897-6878233024e4',
                        '5d1a7710-d38a-4f89-abcf-fd1bcfc46f6f',
                    ]
                ),
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops removed', $responseContent->message);

        $this->assertCount(3, $responseContent->data->id);
        $this->assertEqualsCanonicalizing(self::SHOP_EXISTS_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldRemoveAMaxOf100ShopsId(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => array_merge(
                    array_fill(0, 100, '5d1a7710-d38a-4f89-abcf-fd1bcfc46f6f'),
                    self::SHOP_EXISTS_ID
                ),
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop not found', $responseContent->message);
    }

    /** @test */
    public function itShouldFailRemovingShopsGroupIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'shops_id' => self::SHOP_EXISTS_ID,
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
    public function itShouldFailRemovingShopsGroupNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'group not exists',
                'shops_id' => self::SHOP_EXISTS_ID,
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
    public function itShouldFailRemovingShopsShopIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shops_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->shops_id_empty);
    }

    /** @test */
    public function itShouldFailRemovingShopsShopIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
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

    /** @test */
    public function itShouldFailRemovingShopsNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => ['fc4f5962-02d1-4d80-b3ae-8aeffbcb6268'],
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
    public function itShouldFailRemovingShopsGroupNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'fc4f5962-02d1-4d80-b3ae-8aeffbcb6268',
                'shops_id' => self::SHOP_EXISTS_ID,
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
    public function itShouldFailRemovingShopsUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_HAS_NO_GROUP_EMAIL, self::USER_HAS_NO_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'shops_id' => self::SHOP_EXISTS_ID,
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

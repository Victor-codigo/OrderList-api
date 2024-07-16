<?php

declare(strict_types=1);

namespace Test\Functional\Shop\Adapter\Http\Controller\ShopRemoveAllGroupsShops;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShopRemoveAllGroupsShopsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/shops/group/remove-shops';
    private const string METHOD = 'DELETE';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_EXISTS_ID_2 = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_EXISTS_ID_NO_SHOPS = '0ae87143-9623-318c-8ea1-baaf24253f0f';
    private const array SHOP_EXISTS_ID = [
        'e6c1d350-f010-403c-a2d4-3865c14630ec',
        'cc7f5dd6-02ba-4bd9-b5c1-5b65d81e59a0',
        'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        'dc3d74ab-3d4d-4cf8-b7c2-020642778678',
        'b9b1c541-d41e-4751-9ecb-4a1d823c0405',
    ];
    private const string SYSTEM_KEY = 'systemKeyForDev';

    /** @test */
    public function itShouldRemoveAllGroupsShops(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID,
                    self::GROUP_EXISTS_ID_2,
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups shops removed', $responseContent->message);

        $this->assertEqualsCanonicalizing(self::SHOP_EXISTS_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldRemoveOneGroupShops(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID_2,
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups shops removed', $responseContent->message);

        $this->assertEqualsCanonicalizing([self::SHOP_EXISTS_ID[3]], $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailNoShopsNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID_NO_SHOPS,
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGroupsIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->groups_id_empty);
    }

    /** @test */
    public function itShouldFailGroupsIdIsEmpty(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->groups_id_empty);
    }

    /** @test */
    public function itShouldFailGroupsIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID,
                    self::GROUP_EXISTS_ID_2,
                    'wrong id',
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->groups_id);
    }

    /** @test */
    public function itShouldFailSystemKeyNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID,
                    self::GROUP_EXISTS_ID_2,
                ],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->system_key);
    }

    /** @test */
    public function itShouldFailSystemKeyWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID,
                    self::GROUP_EXISTS_ID_2,
                ],
                'system_key' => 'wrong key',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong system key', $responseContent->message);

        $this->assertEquals('Wrong system key', $responseContent->errors->system_key);
    }
}

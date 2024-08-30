<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductRemoveAllGroupsProducts;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductRemoveAllGroupsProductsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/products/group/remove-products';
    private const string METHOD = 'DELETE';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_EXISTS_ID_2 = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_EXISTS_ID_NO_PRODUCTS = '0ae87143-9623-318c-8ea1-baaf24253f0f';
    private const array PRODUCT_EXISTS_ID = [
        '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
        '8b6d650b-7bb7-4850-bf25-36cda9bce801',
        'afc62bc9-c42c-4c4d-8098-09ce51414a92',
        'faeb885d-d6c4-466d-ac4a-793d87e03e86',
        'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
    ];
    private const string SYSTEM_KEY = 'systemKeyForDev';

    #[Test]
    public function itShouldRemoveAllGroupsProducts(): void
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
        $this->assertSame('Groups products removed', $responseContent->message);

        $this->assertEqualsCanonicalizing(self::PRODUCT_EXISTS_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldRemoveOneGroupProducts(): void
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
        $this->assertSame('Groups products removed', $responseContent->message);

        $this->assertEqualsCanonicalizing([self::PRODUCT_EXISTS_ID[3]], $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailNoProductsNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id' => [
                    self::GROUP_EXISTS_ID_NO_PRODUCTS,
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

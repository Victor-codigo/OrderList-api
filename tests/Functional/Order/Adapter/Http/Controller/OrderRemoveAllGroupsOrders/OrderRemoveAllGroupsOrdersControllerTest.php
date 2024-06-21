<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderRemoveAllGroupsOrders;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderRemoveAllGroupsOrdersControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/orders/group/remove-change';
    private const METHOD = 'DELETE';
    private const USER_ID = 'a3ca650a-a26a-4198-9317-ff3797dcca25';
    private const SYSTEM_KEY = 'systemKeyForDev';
    private const GROUP_ID_1 = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_2 = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const ORDERS_ID_GROUP_1 = [
        '5cfe52e5-db78-41b3-9acd-c3c84924cb9b',
        '72f2f46d-3f3f-48d0-b4eb-5cbed7896cab',
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
        'fad53d41-d396-4f5b-91c3-d30fd6b66845',
    ];
    private const ORDERS_ID_GROUP_2 = [
        '376008f2-4d7e-4072-8bd3-1e42ebe0c6da',
    ];

    /** @test */
    public function itShouldRemoveGroupsIdOrders(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                    self::GROUP_ID_2,
                ],
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['orders_id_removed', 'orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders removed and changed user', $responseContent->message);

        $this->assertEqualsCanonicalizing(
            [...self::ORDERS_ID_GROUP_1, ...self::ORDERS_ID_GROUP_2],
            $responseContent->data->orders_id_removed
        );
        $this->assertEmpty($responseContent->data->orders_id_user_changed);
    }

    /** @test */
    public function itShouldChangeAllGroupsOrdersUserId(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_change_user_id' => [
                    self::GROUP_ID_1,
                    self::GROUP_ID_2,
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['orders_id_removed', 'orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->orders_id_removed);
        $this->assertEqualsCanonicalizing(
            [...self::ORDERS_ID_GROUP_1, ...self::ORDERS_ID_GROUP_2],
            $responseContent->data->orders_id_user_changed
        );
    }

    /** @test */
    public function itShouldRemoveOrdersAndChangeOrdersUserId(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                ],
                'groups_id_change_user_id' => [
                    self::GROUP_ID_2,
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['orders_id_removed', 'orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders removed and changed user', $responseContent->message);

        $this->assertEqualsCanonicalizing(self::ORDERS_ID_GROUP_1, $responseContent->data->orders_id_removed);
        $this->assertEqualsCanonicalizing(self::ORDERS_ID_GROUP_2, $responseContent->data->orders_id_user_changed);
    }

    /** @test */
    public function itShouldNotRemoveOrChangeNoGroupsIdProvided(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [],
                'groups_id_change_user_id' => [],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['orders_id_removed', 'orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->orders_id_removed);
        $this->assertEmpty($responseContent->data->orders_id_user_changed);
    }

    /** @test */
    public function itShouldNotRemoveOrChangeGroupsIdProvidedNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    '65bf6375-3b2b-4e18-bc43-1e144cd6aada',
                ],
                'groups_id_change_user_id' => [
                    '8eb14407-3232-4a5d-aec9-ba83ee17e32a',
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['orders_id_removed', 'orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->orders_id_removed);
        $this->assertEmpty($responseContent->data->orders_id_user_changed);
    }

    /** @test */
    public function itShouldFailGroupsIdToRemoveAreWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                    'wrong id',
                ],
                'groups_id_change_user_id' => [
                    self::GROUP_ID_2,
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id_remove'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->groups_id_remove);
    }

    /** @test */
    public function itShouldFailGroupsIdToChangeUserIdAreWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                ],
                'groups_id_change_user_id' => [
                    self::GROUP_ID_2,
                    'wrong id',
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id_change_user_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->groups_id_change_user_id);
    }

    /** @test */
    public function itShouldFailSystemKeyIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                ],
                'groups_id_change_user_id' => [
                    self::GROUP_ID_2,
                ],
                'user_id_set' => self::USER_ID,
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
    public function itShouldFailSystemKeyIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'groups_id_remove' => [
                    self::GROUP_ID_1,
                ],
                'groups_id_change_user_id' => [
                    self::GROUP_ID_2,
                ],
                'user_id_set' => self::USER_ID,
                'system_key' => 'system key wrong',
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

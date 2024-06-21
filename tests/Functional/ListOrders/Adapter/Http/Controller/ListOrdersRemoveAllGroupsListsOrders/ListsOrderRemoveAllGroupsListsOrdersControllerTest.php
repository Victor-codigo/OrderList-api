<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersRemoveAllGroupsListsOrders;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListsOrderRemoveAllGroupsListsOrdersControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/list-orders/group/remove-change';
    private const METHOD = 'DELETE';
    private const USER_ID = 'a3ca650a-a26a-4198-9317-ff3797dcca25';
    private const SYSTEM_KEY = 'systemKeyForDev';
    private const GROUP_ID_1 = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_2 = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const LIST_ORDERS_ID_GROUP_1 = [
        'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
        'd446eab9-5199-48d0-91f5-0407a86bcb4f',
        'f1559a23-2f92-4660-a335-b1052d7395da',
        'f2980f67-4eb9-41ca-b452-ffa2c7da6a37',
    ];
    private const LIST_ORDERS_ID_GROUP_2 = [
        '0c87f10f-0a0d-4fcc-adae-5cfe93d28c63',
    ];

    /** @test */
    public function itShouldRemoveGroupsIdListsOrders(): void
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

        $this->assertResponseStructureIsOk($response, ['lists_orders_id_removed', 'lists_orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('lists of orders removed and changed user', $responseContent->message);

        $this->assertEqualsCanonicalizing(
            [...self::LIST_ORDERS_ID_GROUP_1, ...self::LIST_ORDERS_ID_GROUP_2],
            $responseContent->data->lists_orders_id_removed
        );
        $this->assertEmpty($responseContent->data->lists_orders_id_user_changed);
    }

    /** @test */
    public function itShouldChangeAllGroupsListsOrdersUserId(): void
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

        $this->assertResponseStructureIsOk($response, ['lists_orders_id_removed', 'lists_orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('lists of orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->lists_orders_id_removed);
        $this->assertEqualsCanonicalizing(
            [...self::LIST_ORDERS_ID_GROUP_1, ...self::LIST_ORDERS_ID_GROUP_2],
            $responseContent->data->lists_orders_id_user_changed
        );
    }

    /** @test */
    public function itShouldRemoveListsOrdersAndChangeListsOrdersUserId(): void
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

        $this->assertResponseStructureIsOk($response, ['lists_orders_id_removed', 'lists_orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('lists of orders removed and changed user', $responseContent->message);

        $this->assertEqualsCanonicalizing(self::LIST_ORDERS_ID_GROUP_1, $responseContent->data->lists_orders_id_removed);
        $this->assertEqualsCanonicalizing(self::LIST_ORDERS_ID_GROUP_2, $responseContent->data->lists_orders_id_user_changed);
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

        $this->assertResponseStructureIsOk($response, ['lists_orders_id_removed', 'lists_orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('lists of orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->lists_orders_id_removed);
        $this->assertEmpty($responseContent->data->lists_orders_id_user_changed);
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

        $this->assertResponseStructureIsOk($response, ['lists_orders_id_removed', 'lists_orders_id_user_changed'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('lists of orders removed and changed user', $responseContent->message);

        $this->assertEmpty($responseContent->data->lists_orders_id_removed);
        $this->assertEmpty($responseContent->data->lists_orders_id_user_changed);
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

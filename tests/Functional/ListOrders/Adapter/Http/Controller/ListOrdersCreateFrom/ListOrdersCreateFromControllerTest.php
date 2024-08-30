<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersCreateFrom;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersCreateFromControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/list-orders/create-from';
    private const string METHOD = 'POST';
    private const string LIST_ORDERS_CREATE_FROM_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    #[Test]
    public function itShouldCreateListOrdersFromOther(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List order created from other list of orders', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherListOrdersIdIsNull(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id_create_from'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->list_orders_id_create_from);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherListOrdersIdIsWrong(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => 'wrong id',
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id_create_from'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id_create_from);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherGroupIdIsNull(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'name' => $listOrderNewName,
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
    public function itShouldCreateListOrdersFromOtherGroupIdIsWrong(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => 'wrong id',
                'name' => $listOrderNewName,
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
    public function itShouldCreateListOrdersFromOtherNameIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => self::GROUP_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherNameIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'wrong-name',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherListOrdersIdNotFound(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => 'b47015cf-dc24-4ad8-bc79-99c3065fd1e2',
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_create_from_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The list orders id to create from, not found', $responseContent->message);
        $this->assertEquals('The list orders id to create from, not found', $responseContent->errors->list_orders_create_from_not_found);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherListOrdersNameAlreadyExists(): void
    {
        $listOrderNewName = 'List order name 1';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name_exists'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The name already exists', $responseContent->message);
        $this->assertEquals('The name already exists', $responseContent->errors->name_exists);
    }

    #[Test]
    public function itShouldCreateListOrdersFromOtherUserNotBelongsToTheGroup(): void
    {
        $listOrderNewName = 'list orders new name';
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id_create_from' => self::LIST_ORDERS_CREATE_FROM_ID,
                'group_id' => self::GROUP_ID,
                'name' => $listOrderNewName,
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

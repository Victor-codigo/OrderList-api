<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\LIstOrderModify;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/list-orders';
    private const string METHOD = 'PUT';

    private function getListOrders(): array
    {
        return [
            'list_orders_id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
            'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'name' => 'list orders name',
            'description' => 'list orders description',
            'date_to_buy' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }

    #[Test]
    public function itShouldModifyListOrders(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders modified', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyListOrdersDescriptionAndDateToBuyAreNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => null,
                'date_to_buy' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders modified', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyListOrdersDateToBuyAreIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => null,
                'date_to_buy' => 'wrong date',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders modified', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldFailModifyingListOrdersListOrdersIsNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => null,
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersListOrdersIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => 'wrong id',
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersGroupIdIsNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => null,
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersGroupIdIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => 'wrong id',
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersNameIsNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => null,
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersNameIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => str_pad('', 51, 'p'),
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldFailModifyingListOrdersDescriptionIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => str_pad('', 501, 'p'),
                'date_to_buy' => $ordersData['date_to_buy'],
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
    public function itShouldFailModifyingListOrdersUserDoesNotBelongToTheGroup(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }

    #[Test]
    public function itShouldFailModifyingListOrdersListOrderIdNotFound(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => '9ef9ce00-adfa-4cf4-adf7-7e1c6843e66a',
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List of orders not found', $responseContent->message);
        $this->assertEquals('List of orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailModifyingListOrdersNameIsAlreadyRegistered(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $ordersData['list_orders_id'],
                'group_id' => $ordersData['group_id'],
                'name' => 'List order name 3',
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_name_exists'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The name is already registered', $responseContent->message);
        $this->assertEquals('The name is already registered', $responseContent->errors->list_orders_name_exists);
    }
}

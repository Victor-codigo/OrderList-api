<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersAddOrder;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersAddOrderControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/list-orders/order';
    private const METHOD = 'POST';
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDER_ID_1 = '9a48ac5b-4571-43fd-ac80-28b08124ffb8';
    private const ORDER_ID_2 = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const ORDER_ID_NOT_IN_THE_LIST_1 = 'c3734d1c-8b18-4bfd-95aa-06a261476d9d';
    private const ORDER_ID_NOT_IN_THE_LIST_2 = '5cfe52e5-db78-41b3-9acd-c3c84924cb9b';

    private function getListOrdersOrdersNewAdd(): array
    {
        return [
            [
                'order_id' => self::ORDER_ID_NOT_IN_THE_LIST_1,
                'bought' => true,
            ],
            [
                'order_id' => self::ORDER_ID_NOT_IN_THE_LIST_2,
                'bought' => false,
            ],
        ];
    }

    private function getListOrdersOrdersAddAlreadyInListOrders(): array
    {
        return [
            [
                'order_id' => self::ORDER_ID_1,
                'bought' => true,
            ],
            [
                'order_id' => self::ORDER_ID_2,
                'bought' => false,
            ],
        ];
    }

    private function listOrdersOrdersAddListOrderId(array $ordersBought): array
    {
        array_walk(
            $ordersBought,
            fn (array &$orderBought) => $orderBought['list_orders_id'] = self::LIST_ORDERS_ID
        );

        return $ordersBought;
    }

    /** @test */
    public function itShouldAddAnOrderToTheListOfOrders(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Added order to list of orders', $responseContent->message);

        foreach ($responseContent->data as $listOrdersOrderCreated) {
            $this->assertContainsEquals((array) $listOrdersOrderCreated, $this->listOrdersOrdersAddListOrderId($ordersToAdd));
        }
    }

    /** @test */
    public function itShouldAddAnOrderToTheListOfOrdersBoughtIsNotSet(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        unset($ordersToAdd[0]['bought']);
        unset($ordersToAdd[1]['bought']);
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Added order to list of orders', $responseContent->message);
        $listOrdersOrdersExpected = $this->listOrdersOrdersAddListOrderId($ordersToAdd);
        $listOrdersOrdersExpected[0]['bought'] = false;
        $listOrdersOrdersExpected[1]['bought'] = false;

        foreach ($responseContent->data as $listOrdersOrderCreated) {
            $this->assertContainsEquals((array) $listOrdersOrderCreated, $listOrdersOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersListOrdersIdIsNull(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => null,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersListOrdersIdIsWrong(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => 'wrong id',
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersListOrdersIsNotInDatabase(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => 'e7a1b1e3-063f-4be7-a022-1bc6ac9d348d',
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('list of orders not found', $responseContent->message);

        $this->assertEquals('list of orders not found', $responseContent->errors->list_orders_not_found);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersListGroupIdIsNull(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => null,
                'orders' => $ordersToAdd,
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
    public function itShouldFailAddingAnOrderToTheListOfOrdersListGroupIdIsWrong(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => 'wrong id',
                'orders' => $ordersToAdd,
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
    public function itShouldFailAddingAnOrderToTheListOfOrdersOrderIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->orders_id_empty);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersOrderIdIsNull(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $ordersToAdd[0]['order_id'] = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['not_blank', 'not_null']], $responseContent->errors->orders);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersOrderIdIsWrong(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $ordersToAdd[1]['order_id'] = 'wrong id';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->orders);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersOrderIdAreAlreadyInOrderList(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersAddAlreadyInListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_already_exists'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('All orders, are already in the list of orders', $responseContent->message);

        $this->assertEquals('All orders, are already in the list of orders', $responseContent->errors->orders_already_exists);
    }

    /** @test */
    public function itShouldFailAddingAnOrderToTheListOfOrdersUserDoesNotBelongsToTheGroup(): void
    {
        $ordersToAdd = $this->getListOrdersOrdersNewAdd();
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
                'group_id' => self::GROUP_ID,
                'orders' => $ordersToAdd,
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

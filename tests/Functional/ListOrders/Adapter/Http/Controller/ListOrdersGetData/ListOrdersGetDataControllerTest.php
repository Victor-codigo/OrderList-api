<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Domain\Model\ListOrders;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/list-orders';
    private const string METHOD = 'GET';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const array LIST_ORDERS_ID = [
        'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
        'd446eab9-5199-48d0-91f5-0407a86bcb4f',
    ];

    private function assertListOrderDataIsOk(ListOrders $listOrderDataExpected, array $listOrderDataActual): void
    {
        $this->assertArrayHasKey('id', $listOrderDataActual);
        $this->assertArrayHasKey('user_id', $listOrderDataActual);
        $this->assertArrayHasKey('group_id', $listOrderDataActual);
        $this->assertArrayHasKey('name', $listOrderDataActual);
        $this->assertArrayHasKey('description', $listOrderDataActual);
        $this->assertArrayHasKey('date_to_buy', $listOrderDataActual);
        $this->assertArrayHasKey('created_on', $listOrderDataActual);

        $this->assertEquals($listOrderDataExpected->getId()->getValue(), $listOrderDataActual['id']);
        $this->assertEquals($listOrderDataExpected->getUserId()->getValue(), $listOrderDataActual['user_id']);
        $this->assertEquals($listOrderDataExpected->getGroupId()->getValue(), $listOrderDataActual['group_id']);
        $this->assertEquals($listOrderDataExpected->getName()->getValue(), $listOrderDataActual['name']);
        $this->assertEquals($listOrderDataExpected->getDescription()->getValue(), $listOrderDataActual['description']);

        $this->assertEquals($listOrderDataExpected->getDateToBuy()->getValue()?->format('Y-m-d H:i:s'), $listOrderDataActual['date_to_buy']);
        $this->assertIsString($listOrderDataActual['created_on']);
    }

    /**
     * @param ListOrders[] $listOrders
     */
    private function assertResponseIsOK(array $listOrders, object $responseContent): void
    {
        $this->assertTrue(property_exists($responseContent->data, 'page'));
        $this->assertTrue(property_exists($responseContent->data, 'pages_total'));
        $this->assertTrue(property_exists($responseContent->data, 'list_orders'));

        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(1, $responseContent->data->pages_total);

        foreach ($responseContent->data->list_orders as $listOrderData) {
            $listOrderData = (array) $listOrderData;
            $this->assertListOrderDataIsOk($listOrders[$listOrderData['id']], $listOrderData);
        }
    }

    private function getListOrders(): array
    {
        return [
            'ba6bed75-4c6e-4ac3-8787-5bded95dac8d' => ListOrders::fromPrimitives(
                'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'List order name 1',
                'List order description 1',
                \DateTime::createFromFormat('Y-m-d H:i:s', '2023-05-29 10:20:15'),
            ),
            'd446eab9-5199-48d0-91f5-0407a86bcb4f' => ListOrders::fromPrimitives(
                'd446eab9-5199-48d0-91f5-0407a86bcb4f',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'List order name 2',
                'List order description 2',
                \DateTime::createFromFormat('Y-m-d H:i:s', '2023-05-28 10:20:15'),
            ),
            'f1559a23-2f92-4660-a335-b1052d7395da' => ListOrders::fromPrimitives(
                'f1559a23-2f92-4660-a335-b1052d7395da',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'List order name 4',
                null,
                null
            ),
            'f2980f67-4eb9-41ca-b452-ffa2c7da6a37' => ListOrders::fromPrimitives(
                'f2980f67-4eb9-41ca-b452-ffa2c7da6a37',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'List order name 3',
                'List order description 3',
                \DateTime::createFromFormat('Y-m-d H:i:s', '2023-05-27 10:20:15'),
            ),
        ];
    }

    /** @test */
    public function itShouldGetDataOfListOrders(): void
    {
        $listOrders = $this->getListOrders();
        $listOrders = array_slice($listOrders, 0, 2, true);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_id='.implode(',', self::LIST_ORDERS_ID)
                .'&order_asc=true'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'list_orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(2, $responseContent->data->list_orders);
        $this->assertResponseIsOK($listOrders, $responseContent);
    }

    /** @test */
    public function itShouldGetDataOfListOrdersListOrdersOfAGroup(): void
    {
        $listOrders = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'list_orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(4, $responseContent->data->list_orders);
        $this->assertResponseIsOK($listOrders, $responseContent);
    }

    /** @test */
    public function itShouldGetDataOfListOrdersListOrdersIdOnlyProcess100(): void
    {
        $listOrders = $this->getListOrders();
        $listOrders = array_slice($listOrders, 0, 1, true);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_id='.implode(',', array_fill(0, 100, self::LIST_ORDERS_ID[0])).','.self::LIST_ORDERS_ID[1]
                .'&order_asc=true'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'list_orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(1, $responseContent->data->list_orders);
        $this->assertResponseIsOK($listOrders, $responseContent);
    }

    /** @test */
    public function itShouldGetDataOfListOrdersFilterSection(): void
    {
        $listOrders = $this->getListOrders();
        $listOrders = array_slice($listOrders, 0, 1, true);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'list_orders'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(1, $responseContent->data->list_orders);
        $this->assertResponseIsOK($listOrders, $responseContent);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.implode(',', self::LIST_ORDERS_ID)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id=wrong id'
                .'&list_orders_id='.implode(',', self::LIST_ORDERS_ID)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_id=wrong id'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_id=d76cf85a-9b1e-45f9-8fa1-6a04fd552171'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersFilterSectionIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=wrong filter'
                .'&filter_text=equals'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['section_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersFilterTextIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=wrong filter'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['text_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersFilterSectionAndTextAreNotPresent(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersFilterValueIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List -'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['text_filter_value', 'text_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersPageIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersPageIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page=-1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersPageItemsIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page=1'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersPageItemsIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&order_asc=true'
                .'&filter_value=List order name 1'
                .'&filter_section=list_orders'
                .'&filter_text=equals'
                .'&page=1'
                .'&page_items=-1'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&page=1'
                .'&page_items=10'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

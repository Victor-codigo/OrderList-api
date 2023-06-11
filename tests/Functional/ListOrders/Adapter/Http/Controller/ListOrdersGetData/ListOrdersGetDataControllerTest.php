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

    private const ENDPOINT = '/api/v1/list-orders';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const LIST_ORDERS_ID = [
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
        $this->assertEquals($listOrderDataExpected->getDateToBuy()->getValue()->format('Y-m-d H:i:s'), $listOrderDataActual['date_to_buy']);
        $this->assertIsString($listOrderDataActual['created_on']);
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
        ];
    }

    /** @test */
    public function itShouldGetDataOfListOrders(): void
    {
        $listOrders = $this->getListOrders();
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_ids='.implode(',', self::LIST_ORDERS_ID)
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(2, $responseContent->data);

        foreach ($responseContent->data as $listOrderData) {
            $listOrderData = (array) $listOrderData;
            $this->assertListOrderDataIsOk($listOrders[$listOrderData['id']], $listOrderData);
        }
    }

    /** @test */
    public function itShouldGetDataOfListOrdersListOrdersNameStartsWithIsNull(): void
    {
        $listOrders = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_ids='.implode(',', self::LIST_ORDERS_ID)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(2, $responseContent->data);

        foreach ($responseContent->data as $listOrderData) {
            $listOrderData = (array) $listOrderData;
            $this->assertListOrderDataIsOk($listOrders[$listOrderData['id']], $listOrderData);
        }
    }

    /** @test */
    public function itShouldGetDataOfListOrdersListOrdersIdsIsNull(): void
    {
        $listOrders = $this->getListOrders();
        $listOrdersStartsWith = 'List order name 1';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(1, $responseContent->data);

        foreach ($responseContent->data as $listOrderData) {
            $listOrderData = (array) $listOrderData;
            $this->assertListOrderDataIsOk($listOrders[$listOrderData['id']], $listOrderData);
        }
    }

    /** @test */
    public function itShouldGetDataOfListOrdersListOrdersIdOnlyProcess100(): void
    {
        $listOrders = $this->getListOrders();
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_ids='.implode(',', array_fill(0, 100, self::LIST_ORDERS_ID[0])).','.self::LIST_ORDERS_ID[1]
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders data', $responseContent->message);

        $this->assertCount(1, $responseContent->data);

        foreach ($responseContent->data as $listOrderData) {
            $listOrderData = (array) $listOrderData;
            $this->assertListOrderDataIsOk($listOrders[$listOrderData['id']], $listOrderData);
        }
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersIdAndNameStartsWithAreNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_ids_and_name_starts_with_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->list_orders_ids_and_name_starts_with_empty);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersIdAndNameStartsWithAreWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id=wrong id'
                .'&list_orders_ids='.implode(',', self::LIST_ORDERS_ID)
                .'&list_orders_name_starts_with='.str_pad('', 51, 'p'),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id', 'list_orders_name_starts_with'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
        $this->assertEquals(['string_too_long'], $responseContent->errors->list_orders_name_starts_with);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersGroupIdIsNull(): void
    {
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_ids='.implode(',', self::LIST_ORDERS_ID)
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
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
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id=wrong id'
                .'&list_orders_ids='.implode(',', self::LIST_ORDERS_ID)
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
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
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_ids=wrong id'
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_ids'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->list_orders_ids);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersIdNotFound(): void
    {
        $listOrdersStartsWith = 'list order name not exists';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_ids=d76cf85a-9b1e-45f9-8fa1-6a04fd552171'
                ."&list_orders_name_starts_with={$listOrdersStartsWith}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders ids not found', $responseContent->message);
        $this->assertEquals('List orders ids not found', $responseContent->errors->list_orders_not_found);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersNameStartsWithNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_name_starts_with=not found',
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders ids not found', $responseContent->message);
        $this->assertEquals('List orders ids not found', $responseContent->errors->list_orders_not_found);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersListOrdersNameStartsWithWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_name_starts_with='.str_pad('', 51, 'p'),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_name_starts_with'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->list_orders_name_starts_with);
    }

    /** @test */
    public function itShouldFailGettingDataOfListOrdersUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                .'&list_orders_name_starts_with=not found',
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

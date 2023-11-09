<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/orders';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
    ];

    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_EXISTS_USER_NOT_BELONGS = '4d52266f-aa7e-324e-b92d-6152635dd09e';

    private function getOrdersDataExpected(): array
    {
        return [
             self::ORDERS_ID[0] => [
                'id' => self::ORDERS_ID[0],
                'product_id' => 'afc62bc9-c42c-4c4d-8098-09ce51414a92',
                'shop_id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'description' => 'order description',
                'amount' => 10.200,
                'unit' => UNIT_MEASURE_TYPE::UNITS->value,
                'created_on' => '2023-05-29 13:35:10',
                'price' => 10.50,
             ],
             self::ORDERS_ID[1] => [
                'id' => self::ORDERS_ID[1],
                'product_id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'shop_id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                'description' => 'order description 2',
                'amount' => 20.050,
                'unit' => UNIT_MEASURE_TYPE::KG->value,
                'created_on' => '2023-05-29 13:35:10',
                'price' => 20.30,
            ],
        ];
    }

    private function assertOrderDataIsOk(array $orderExpected, object $orderDataActual): void
    {
        $this->assertTrue(property_exists($orderDataActual, 'id'));
        $this->assertTrue(property_exists($orderDataActual, 'product_id'));
        $this->assertTrue(property_exists($orderDataActual, 'shop_id'));
        $this->assertTrue(property_exists($orderDataActual, 'user_id'));
        $this->assertTrue(property_exists($orderDataActual, 'group_id'));
        $this->assertTrue(property_exists($orderDataActual, 'description'));
        $this->assertTrue(property_exists($orderDataActual, 'amount'));
        $this->assertTrue(property_exists($orderDataActual, 'unit'));
        $this->assertTrue(property_exists($orderDataActual, 'created_on'));
        $this->assertTrue(property_exists($orderDataActual, 'price'));

        $this->assertEquals($orderExpected['id'], $orderDataActual->id);
        $this->assertEquals($orderExpected['product_id'], $orderDataActual->product_id);
        $this->assertEquals($orderExpected['shop_id'], $orderDataActual->shop_id);
        $this->assertEquals($orderExpected['user_id'], $orderDataActual->user_id);
        $this->assertEquals($orderExpected['group_id'], $orderDataActual->group_id);
        $this->assertEquals($orderExpected['description'], $orderDataActual->description);
        $this->assertEquals($orderExpected['amount'], $orderDataActual->amount);
        $this->assertEquals($orderExpected['unit'], $orderDataActual->unit);
        $this->assertIsString($orderDataActual->created_on);
        $this->assertEquals($orderExpected['price'], $orderDataActual->price);
    }

    /** @test */
    public function itShouldGetOrdersData(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $groupId = self::GROUP_ID;
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertIsArray($responseContent->data);
        $this->assertCount(count(self::ORDERS_ID), $responseContent->data);

        foreach ($responseContent->data as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataTwoOrdersOfThree(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $ordersId .= ',5170ab8a-a359-4ef8-b07a-866500b731e5';
        $groupId = self::GROUP_ID;
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertIsArray($responseContent->data);
        $this->assertCount(count(self::ORDERS_ID), $responseContent->data);

        foreach ($responseContent->data as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataOnlyOneOf101(): void
    {
        $ordersId = array_fill(0, 100, self::ORDERS_ID[0]);
        $ordersId[] = self::ORDERS_ID[1];
        $ordersId = implode(',', $ordersId);
        $groupId = self::GROUP_ID;
        $ordersDataExpected = $this->getOrdersDataExpected();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Orders\' data', $responseContent->message);
        $this->assertIsArray($responseContent->data);
        $this->assertCount(1, $responseContent->data);

        foreach ($responseContent->data as $orderData) {
            $this->assertTrue(property_exists($orderData, 'id'));
            $this->assertOrderDataIsOk($ordersDataExpected[$orderData->id], $orderData);
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersGroupIdIsNull(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersGroupIdIsWrong(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $groupId = 'wrong id';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingOrdersOrdersIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->orders_id_empty);
    }

    /** @test */
    public function itShouldFailGettingOrdersOrdersIdIsWrong(): void
    {
        $ordersId = 'wrong id';
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->orders_id);
    }

    /** @test */
    public function itShouldFailGetOrdersDataOrdersNotFound(): void
    {
        $ordersId = '5170ab8a-a359-4ef8-b07a-866500b731e5';
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Orders not found', $responseContent->message);
        $this->assertEquals('Orders not found', $responseContent->errors->order_not_found);
    }

    /** @test */
    public function itShouldFailGetOrdersDataUserNotBelongsToTheGroup(): void
    {
        $ordersId = implode(',', self::ORDERS_ID);
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?orders_id={$ordersId}&group_id={$groupId}",
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

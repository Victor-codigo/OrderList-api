<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersGetPrice;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersGetPriceControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/list-orders/price';
    private const METHOD = 'GET';

    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const LIST_ORDERS_PRODUCTS_HAS_NO_PRICE_ID = 'f1559a23-2f92-4660-a335-b1052d7395da';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRICE_TOTAL = 514.115;
    private const PRICE_BOUGHT = 407.015;

    /** @test */
    public function itShouldGetListOrdersPrice(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.self::LIST_ORDERS_ID
                .'&group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['total', 'bought'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders price', $responseContent->message);
        $this->assertEquals(self::PRICE_TOTAL, round($responseContent->data->total, 3));
        $this->assertEquals(self::PRICE_BOUGHT, round($responseContent->data->bought, 3));
    }

    /** @test */
    public function itShouldGetListOrdersPriceAllOrdersHasNotPrice(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.self::LIST_ORDERS_PRODUCTS_HAS_NO_PRICE_ID
                .'&group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['total', 'bought'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders price', $responseContent->message);
        $this->assertEquals(0, $responseContent->data->total);
        $this->assertEquals(0, $responseContent->data->bought);
    }

    /** @test */
    public function itShouldFailGettingListOrdersPriceListOrdersIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailGettingListOrdersPriceListOrdersIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id=wrong id'
                .'&group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id);
    }

    /** @test */
    public function itShouldFailGettingListOrdersPriceListOrdersIdIsNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id=e98f3b5d-9823-4677-953f-cfe80ad1d31c'
                .'&group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List of orders not found', $responseContent->message);

        $this->assertEquals('List of orders not found', $responseContent->errors->list_orders_not_found);
    }

    /** @test */
    public function itShouldFailGettingListOrdersPriceGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.self::LIST_ORDERS_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingListOrdersPriceGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.self::LIST_ORDERS_ID
                .'&group_id=wrong id'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingListOrdersUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?list_orders_id='.self::LIST_ORDERS_ID
                .'&group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);

        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }
}

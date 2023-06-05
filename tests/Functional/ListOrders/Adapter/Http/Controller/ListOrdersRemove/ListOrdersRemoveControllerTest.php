<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/list-orders';
    private const METHOD = 'DELETE';
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private function getListOrders(): array
    {
        return [
            'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'name' => 'list orders name',
            'description' => 'list orders description',
            'date_to_buy' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }

    /** @test */
    public function itShouldRemoveListOrders(): void
    {
        $listOrdersId = self::LIST_ORDERS_ID;
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $listOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders removed', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldFailRemovingListOrdersListOrdersIdIsNull(): void
    {
        $listOrdersId = null;
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $listOrdersId,
                'group_id' => $groupId,
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
    public function itShouldFailRemovingListOrdersGroupIdIsNull(): void
    {
        $listOrdersId = self::LIST_ORDERS_ID;
        $groupId = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $listOrdersId,
                'group_id' => $groupId,
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
    public function itShouldFailRemovingListOrdersUserDoesNotBelongsToThGroup(): void
    {
        $listOrdersId = self::LIST_ORDERS_ID;
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $listOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailRemovingListOrdersListOrderIdNotFound(): void
    {
        $listOrdersId = '9d1a5942-850f-41f9-a32a-38927978ce5c';
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => $listOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);
        $this->assertEquals('List orders not found', $responseContent->errors->list_orders_not_found);
    }
}

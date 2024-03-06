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
    private const LIST_ORDERS_ID_1 = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const LIST_ORDERS_ID_2 = 'd446eab9-5199-48d0-91f5-0407a86bcb4f';
    private const LIST_ORDERS_ID_3 = 'f2980f67-4eb9-41ca-b452-ffa2c7da6a37';
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
        $listsOrdersId = [
            self::LIST_ORDERS_ID_1,
            self::LIST_ORDERS_ID_2,
        ];
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'lists_orders_id' => $listsOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders removed', $responseContent->message);

        $this->assertCount(2, $responseContent->data->id);

        foreach ($responseContent->data->id as $id) {
            $this->assertContains($id, $listsOrdersId);
        }
    }

    /** @test */
    public function itShouldFailRemovingListsOrdersListsOrdersIdIsNull(): void
    {
        $listsOrdersId = null;
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'lists_orders_id' => $listsOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['lists_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->lists_orders_id);
    }

    /** @test */
    public function itShouldFailRemovingListsOrdersGroupIdIsNull(): void
    {
        $listsOrdersId = [
            self::LIST_ORDERS_ID_1,
            self::LIST_ORDERS_ID_2,
        ];
        $groupId = null;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'lists_orders_id' => $listsOrdersId,
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
    public function itShouldFailRemovingListsOrdersUserDoesNotBelongsToThGroup(): void
    {
        $listsOrdersId = [
            self::LIST_ORDERS_ID_1,
            self::LIST_ORDERS_ID_2,
        ];
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'lists_orders_id' => $listsOrdersId,
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
        $listsOrdersId = ['9d1a5942-850f-41f9-a32a-38927978ce5c'];
        $groupId = self::GROUP_ID;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'lists_orders_id' => $listsOrdersId,
                'group_id' => $groupId,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['lists_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);
        $this->assertEquals('List orders not found', $responseContent->errors->lists_orders_not_found);
    }
}

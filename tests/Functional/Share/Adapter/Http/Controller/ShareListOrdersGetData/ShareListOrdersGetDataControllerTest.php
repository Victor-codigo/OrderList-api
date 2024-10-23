<?php

declare(strict_types=1);

namespace Test\Functional\Share\Adapter\Http\Controller\ShareListOrdersGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShareListOrdersGetDataControllerTest extends WebClientTestCase
{
    private const string METHOD = 'GET';
    private const string ENDPOINT = '/api/v1/share/list-orders';
    private const string SHARE_ID = '72b37f9c-ff55-4581-a131-4270e73012a2';

    /**
     * @param array<int, object{
     *  id: string,
     *  user_id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  date_to_buy: string,
     *  created_on: string
     * }> $listOrdersData
     * @param array{
     *  id: string,
     *  user_id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  date_to_buy: string,
     *  created_on: string
     * } $listOrdersDataExpected
     */
    private function assertListOrdersDataIsOk(array $listOrdersData, array $listOrdersDataExpected): void
    {
        $this->assertEquals($listOrdersDataExpected['id'], $listOrdersData[0]->id);
        $this->assertEquals($listOrdersDataExpected['user_id'], $listOrdersData[0]->user_id);
        $this->assertEquals($listOrdersDataExpected['group_id'], $listOrdersData[0]->group_id);
        $this->assertEquals($listOrdersDataExpected['name'], $listOrdersData[0]->name);
        $this->assertEquals($listOrdersDataExpected['description'], $listOrdersData[0]->description);
        $this->assertEquals($listOrdersDataExpected['date_to_buy'], $listOrdersData[0]->date_to_buy);
        $this->assertNotNull($listOrdersData[0]->created_on);
    }

    /**
     * @return array{
     *  id: string,
     *  user_id: string,
     *  group_id: string,
     *  name: string,
     *  description: string,
     *  date_to_buy: string,
     *  created_on: string
     * }
     */
    private function getListOrdersDataExpected(): array
    {
        return [
            'id' => 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
            'user_id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'name' => 'List order name 1',
            'description' => 'List order description 1',
            'date_to_buy' => '2023-05-29 10:20:15',
            'created_on' => '2024-10-18 10:07:46',
        ];
    }

    #[Test]
    public function itShouldGetListOrdersData(): void
    {
        $listOrdersSharedId = self::SHARE_ID;
        $listOrdersDataExpected = $this->getListOrdersDataExpected();

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$listOrdersSharedId}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders shared data', $responseContent->message);

        $this->assertListOrdersDataIsOk($responseContent->data, $listOrdersDataExpected);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataRecourseListOrdersNotFound(): void
    {
        $recourseId = '6521303a-5657-46e4-881c-8b929543b18f';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shared_list_orders_id={$recourseId}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);

        $this->assertEquals('List orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataListOrdersIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shared_list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shared_list_orders_id);
    }

    #[Test]
    public function itShouldFailGettingListOrdersDataListOrdersIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?shared_list_orders_id=Wrong id',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shared_list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->shared_list_orders_id);
    }
}

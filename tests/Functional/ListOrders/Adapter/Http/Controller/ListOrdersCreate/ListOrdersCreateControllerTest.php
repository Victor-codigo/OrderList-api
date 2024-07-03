<?php

declare(strict_types=1);

namespace Test\Functional\ListOrders\Adapter\Http\Controller\ListOrdersCreate;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ListOrdersCreateControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/list-orders';
    private const string METHOD = 'POST';

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
    public function itShouldCreateListOrders(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List order created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldCreateListOrdersDescriptionANdDateToBuyAreNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => null,
                'date_to_buy' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List order created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldCreateListOrdersDateToBuyIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => $ordersData['group_id'],
                'name' => $ordersData['name'],
                'description' => null,
                'date_to_buy' => '2023-6-3',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List order created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldFailCreatingListOrdersGroupIdIsNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
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

    /** @test */
    public function itShouldFailCreatingListOrdersGroupIdIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
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

    /** @test */
    public function itShouldFailCreatingListOrdersNameIsNull(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
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

    /** @test */
    public function itShouldFailCreatingListOrdersNameIsWrong(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => $ordersData['group_id'],
                'name' => 'name wrong!',
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailCreatingListOrdersNameAlreadyExists(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => $ordersData['group_id'],
                'name' => 'list order name 1',
                'description' => $ordersData['description'],
                'date_to_buy' => $ordersData['date_to_buy'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name_exists'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The name already exists', $responseContent->message);
        $this->assertEquals('The name already exists', $responseContent->errors->name_exists);
    }

    /** @test */
    public function itShouldFailCreatingListOrdersUserDoesNotBelongToTheGroup(): void
    {
        $ordersData = $this->getListOrders();
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
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
}

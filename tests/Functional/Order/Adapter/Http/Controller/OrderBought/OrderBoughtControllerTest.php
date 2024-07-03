<?php

declare(strict_types=1);

namespace Test\Functional\Order\Adapter\Http\Controller\OrderBought;

use Override;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class OrderBoughtControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/orders/bought';
    private const string METHOD = 'PATCH';
    private const string ORDER_ID = '5cfe52e5-db78-41b3-9acd-c3c84924cb9b';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    private function boughtDataProvider(): iterable
    {
        yield [
            '5cfe52e5-db78-41b3-9acd-c3c84924cb9b',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            true,
        ];
        yield [
            '72f2f46d-3f3f-48d0-b4eb-5cbed7896cab',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            false,
        ];
        yield [
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            null,
        ];
    }

    /**
     * @test
     *
     * @dataProvider boughtDataProvider
     */
    public function itShouldSetBought(?string $orderId, ?string $groupId, ?bool $bought): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => $orderId,
                'group_id' => $groupId,
                'bought' => $bought,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Order bought set', $responseContent->message);

        $this->assertEquals($orderId, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailSettingBoughtOrderIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'bought' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->order_id);
    }

    /** @test */
    public function itShouldFailSettingBoughtOrderIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => 'wrong id',
                'group_id' => self::GROUP_ID,
                'bought' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->order_id);
    }

    /** @test */
    public function itShouldFailSettingBoughtOrderIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => '47a79916-b7b8-4ff3-89f6-78279e6cc7fa',
                'group_id' => self::GROUP_ID,
                'bought' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['order_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Order not found', $responseContent->message);

        $this->assertEquals('Order not found', $responseContent->errors->order_not_found);
    }

    /** @test */
    public function itShouldFailSettingBoughtGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'bought' => true,
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
    public function itShouldFailSettingBoughtGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => 'wrong id',
                'bought' => true,
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
    public function itShouldFailSettingBoughtUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'order_id' => self::ORDER_ID,
                'group_id' => self::GROUP_ID,
                'bought' => true,
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

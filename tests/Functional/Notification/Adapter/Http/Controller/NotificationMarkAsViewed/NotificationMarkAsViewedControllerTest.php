<?php

declare(strict_types=1);

namespace Test\Functional\Notification\Adapter\Http\Controller\NotificationMarkAsViewed;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class NotificationMarkAsViewedControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const string ENDPOINT = '/api/v1/notification/mark-as-viewed';
    private const string METHOD = 'PATCH';
    private const array NOTIFICATIONS_ID = [
        '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c',
        'd75a3fb1-42aa-46c0-be4c-1147f0808d60',
        'f7621fbd-0c8e-4a8a-8059-9e87b8ea4fe1',
    ];

    /** @test */
    public function itShouldMarkNotificationsAsViewed(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'notifications_id' => self::NOTIFICATIONS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications marked as viewed', $responseContent->message);

        $this->assertEquals(self::NOTIFICATIONS_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedNotificationsIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'notifications_id' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['notifications_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->notifications_empty);
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedNotificationsIdIsEmpty(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'notifications_id' => [],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['notifications_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->notifications_empty);
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedNotificationsIdAreWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'notifications_id' => [
                    self::NOTIFICATIONS_ID[0],
                    'wrong id 1',
                    'wrong id 2',
                ],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['notifications_id'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([
            ['uuid_invalid_characters'],
            ['uuid_invalid_characters'],
        ],
            $responseContent->errors->notifications_id
        );
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedNotificationsIdNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'notifications_id' => [
                    'eb05e579-de82-42df-8acb-9244cb6a20fe',
                    'f99682b5-9368-4a9c-8335-624bb17064ad',
                    'a761b966-7946-4767-bb08-428d4489247d',
                ],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['notifications_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Notifications not found', $responseContent->message);

        $this->assertEquals('Notifications not found', $responseContent->errors->notifications_not_found);
    }
}

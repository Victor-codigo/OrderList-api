<?php

declare(strict_types=1);

namespace Test\Functional\Notification\Adapter\Http\Controller\NotificationRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class NotificationRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/notification';
    private const string METHOD = 'DELETE';
    private const string NOTIFICATION_ID_1 = '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c';
    private const string NOTIFICATION_ID_2 = 'd75a3fb1-42aa-46c0-be4c-1147f0808d60';
    private const string NOTIFICATION_ID_3 = 'f7621fbd-0c8e-4a8a-8059-9e87b8ea4fe1';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    private function getNotificationsIds(): array
    {
        return [
            self::NOTIFICATION_ID_1,
            self::NOTIFICATION_ID_2,
            self::NOTIFICATION_ID_3,
        ];
    }

    /** @test */
    public function itShouldRemoveNotifications(): void
    {
        $notificationsId = $this->getNotificationsIds();
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications removed', $responseContent->message);
        $this->assertCount(count($notificationsId), $responseContent->data->id);

        foreach ($responseContent->data->id as $notificationId) {
            $this->assertContainsEquals($notificationId, $notificationsId);
        }
    }

    /** @test */
    public function itShouldRemoveMaximumOf100Notifications(): void
    {
        $notificationsId = array_fill(0, 100, self::NOTIFICATION_ID_1);
        $notificationsId[] = self::NOTIFICATION_ID_2;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications removed', $responseContent->message);
        $this->assertCount(1, $responseContent->data->id);

        $this->assertEquals($responseContent->data->id[0], self::NOTIFICATION_ID_1);
    }

    /** @test */
    public function itShouldFailRemoveNotificationsWrongIds(): void
    {
        $notificationsId = [
            self::NOTIFICATION_ID_1,
            'notification wrong',
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['notifications_id'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->notifications_id);
    }

    /** @test */
    public function itShouldFailRemoveNotificationsNotFound(): void
    {
        $notificationsId = [
            '63b6175e-2236-4662-84a8-d03e495ba4c1',
            'fb401ee1-8438-4537-a01e-dcbdc5bdab16',
            '81e0da98-970a-4091-b363-c617538b68c8',
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailRemoveNotificationsNoGrantsToRemove(): void
    {
        $notificationsId = [
            'b04cb546-da1c-31d5-a4f2-00a7a2e85e89',
            'edd664af-0541-35d7-9c23-2a05e64f74c1',
            '0879fabc-838d-3b80-957f-48f87ca1caa9',
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailRemoveNotificationsNotAuthorized(): void
    {
        $notificationsId = [
            '63b6175e-2236-4662-84a8-d03e495ba4c1',
            'fb401ee1-8438-4537-a01e-dcbdc5bdab16',
            '81e0da98-970a-4091-b363-c617538b68c8',
        ];
        $client = $this->getNewClientNoAuthenticated();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?notifications_id='.implode(',', $notificationsId),
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}

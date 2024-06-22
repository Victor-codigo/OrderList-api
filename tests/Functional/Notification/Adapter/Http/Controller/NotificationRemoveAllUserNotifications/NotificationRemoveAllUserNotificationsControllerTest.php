<?php

declare(strict_types=1);

namespace Test\Functional\Notification\Adapter\Http\Controller\NotificationRemoveAllUserNotifications;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class NotificationRemoveAllUserNotificationsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/notification/user/remove-groups';
    private const METHOD = 'DELETE';
    private const SYSTEM_KEY = 'systemKeyForDev';
    private const NOTIFICATION_ID_TO_REMOVE = [
        '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c',
        'd75a3fb1-42aa-46c0-be4c-1147f0808d60',
        'f7621fbd-0c8e-4a8a-8059-9e87b8ea4fe1',
        'f79ddff5-486b-4b5f-af64-b99fe9154fc1',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldRemoveNotifications(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User notifications removed', $responseContent->message);
        $this->assertCount(count(self::NOTIFICATION_ID_TO_REMOVE), $responseContent->data->id);

        $this->assertEqualsCanonicalizing(self::NOTIFICATION_ID_TO_REMOVE, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailNoUserNotifications(): void
    {
        $client = $this->getNewClientAuthenticated('email.other_2.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailSystemKeyIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->system_key);
    }

    /** @test */
    public function itShouldFailSystemKeyIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => 'wrong system key',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('System key is wrong', $responseContent->message);

        $this->assertEquals('System key is wrong', $responseContent->errors->system_key);
    }
}

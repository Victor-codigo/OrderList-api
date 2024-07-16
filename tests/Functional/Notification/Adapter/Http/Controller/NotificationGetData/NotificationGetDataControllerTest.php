<?php

declare(strict_types=1);

namespace Test\Functional\Notification\Adapter\Http\Controller\NotificationGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Notification\Domain\Model\Notification;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class NotificationGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/notification';
    private const string METHOD = 'GET';

    private function getNotificationsDatabaseData(): array
    {
        return [
            Notification::fromPrimitives('84a08f7c-30a6-4bd5-8e5b-b2d49948e72c', '2606508b-4516-45d6-93a6-c7cb416b7f3f', NOTIFICATION_TYPE::USER_REGISTERED, []),
            Notification::fromPrimitives('d75a3fb1-42aa-46c0-be4c-1147f0808d60', '2606508b-4516-45d6-93a6-c7cb416b7f3f', NOTIFICATION_TYPE::USER_REGISTERED, []),
            Notification::fromPrimitives('f7621fbd-0c8e-4a8a-8059-9e87b8ea4fe1', '2606508b-4516-45d6-93a6-c7cb416b7f3f', NOTIFICATION_TYPE::GROUP_USER_ADDED, []),
            Notification::fromPrimitives('f79ddff5-486b-4b5f-af64-b99fe9154fc1', '2606508b-4516-45d6-93a6-c7cb416b7f3f', NOTIFICATION_TYPE::GROUP_USER_ADDED, []),
        ];
    }

    private function assertNotificationIsCorrect(object $notificationData, array $notificationsDataExpected): void
    {
        $notificationsExpectedIds = array_map(fn (Notification $notification): ?string => $notification->getId()->getValue(), $notificationsDataExpected);
        $notificationsExpectedUsersIds = array_map(fn (Notification $notification): ?string => $notification->getUserId()->getValue(), $notificationsDataExpected);
        $notificationsExpectedViewed = array_map(fn (Notification $notification): bool => $notification->getViewed(), $notificationsDataExpected);

        $this->assertTrue(property_exists($notificationData, 'id'));
        $this->assertTrue(property_exists($notificationData, 'user_id'));
        $this->assertTrue(property_exists($notificationData, 'message'));
        $this->assertTrue(property_exists($notificationData, 'viewed'));
        $this->assertTrue(property_exists($notificationData, 'created_on'));

        $this->assertContainsEquals($notificationData->id, $notificationsExpectedIds);
        $this->assertContainsEquals($notificationData->user_id, $notificationsExpectedUsersIds);
        $this->assertContainsEquals($notificationData->viewed, $notificationsExpectedViewed);
    }

    /** @test */
    public function itShouldGetTheDataOfTheUserNotifications(): void
    {
        $page = 1;
        $pageItems = 10;
        $lang = 'en';
        $notificationsDataExpected = $this->getNotificationsDatabaseData();

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2, 3], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications data', $responseContent->message);
        $this->assertCount(count($notificationsDataExpected), $responseContent->data);

        foreach ($responseContent->data as $notificationData) {
            $this->assertNotificationIsCorrect($notificationData, $notificationsDataExpected);
        }
    }

    /** @test */
    public function itShouldGetTheDataOfTheUserNotificationsPaginationPageItemsTwo(): void
    {
        $page = 1;
        $pageItems = 2;
        $notificationsDataExpected = $this->getNotificationsDatabaseData();

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications data', $responseContent->message);
        $this->assertCount(2, $responseContent->data);

        foreach ($responseContent->data as $notificationData) {
            $this->assertNotificationIsCorrect($notificationData, $notificationsDataExpected);
        }
    }

    /** @test */
    public function itShouldGetTheDataOfTheUserNotificationsPaginationPageTwoPageItemsTwo(): void
    {
        $page = 2;
        $pageItems = 2;
        $lang = 'es';
        $notificationsDataExpected = $this->getNotificationsDatabaseData();
        $notificationsDataExpected = [$notificationsDataExpected[2], $notificationsDataExpected[3]];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notifications data', $responseContent->message);
        $this->assertCount(2, $responseContent->data);

        foreach ($responseContent->data as $notificationData) {
            $this->assertNotificationIsCorrect($notificationData, $notificationsDataExpected);
        }
    }

    /** @test */
    public function itShouldGetNoNotificationsUserHasNotNotifications(): void
    {
        $page = 1;
        $pageItems = 10;
        $lang = 'en';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingNotificationDataPageIsZero(): void
    {
        $page = 0;
        $pageItems = 10;
        $lang = 'en';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGettingNotificationDataPageItemsIsZero(): void
    {
        $page = 1;
        $pageItems = 0;
        $lang = 'en';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGettingNotificationDataPageItemsIsGreaterThan100(): void
    {
        $page = 1;
        $pageItems = 101;
        $lang = 'en';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['less_than_or_equal'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGettingNotificationLangIsWrong(): void
    {
        $page = 1;
        $pageItems = 10;
        $lang = 'ru';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}&lang={$lang}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['lang'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['choice_not_such'], $responseContent->errors->lang);
    }

    /** @test */
    public function itShouldFailUserNotAuthorized(): void
    {
        $page = 1;
        $pageItems = 10;

        $client = $this->getNewClientNoAuthenticated();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?page={$page}&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}

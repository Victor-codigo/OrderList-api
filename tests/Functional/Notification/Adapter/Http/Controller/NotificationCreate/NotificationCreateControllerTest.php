<?php

declare(strict_types=1);

namespace Test\Functional\Notification\Adapter\Http\Controller\NotificationCreate;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class NotificationCreateControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const string ENDPOINT = '/api/v1/notification';
    private const string METHOD = 'POST';
    private const string USER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const string USER_2_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string USER_3_ID = '6df60afd-f7c3-4c2c-b920-e265f266c560';

    #[Test]
    public function itShouldCreateANotification(): void
    {
        $usersId = [self::USER_ID];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notification created', $responseContent->message);
        $this->assertCount(count($usersId), $responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateManyNotifications(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notification created', $responseContent->message);
        $this->assertCount(count($usersId), $responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateAUserNotificationNotificationDataIsNull(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Notification created', $responseContent->message);
        $this->assertCount(count($usersId), $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailCreatingAUserNotificationUserIdNotValid(): void
    {
        $usersId = [
            self::USER_ID,
            'not a valid user',
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_id'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->users_id);
    }

    #[Test]
    public function itShouldFailCreatingAUsersNotificationOneUserIdNotFound(): void
    {
        $usersId = [
            self::USER_ID,
            '22fd9f1f-ff4c-4f4a-abca-b0be7f965048',
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_wrong'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong users', $responseContent->message);
        $this->assertEquals('Wrong users', $responseContent->errors->users_wrong);
    }

    #[Test]
    public function itShouldFailCreatingAUsersNotificationNoUsersIdNotFound(): void
    {
        $usersId = [
            '22fd9f1f-ff4c-4f4a-abca-b0be7f965048',
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_wrong'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong users', $responseContent->message);
        $this->assertEquals('Wrong users', $responseContent->errors->users_wrong);
    }

    #[Test]
    public function itShouldFailCreatingAUsersNotificationNoUsersProvided(): void
    {
        $usersId = [];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_id'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->users_id);
    }

    #[Test]
    public function itShouldFailCreatingAUserNotificationTypeWrong(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => 'wrong type',
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['type'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->type);
    }

    #[Test]
    public function itShouldFailCreatingAUserNotificationSystemKeyIsNull(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => null,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->system_key);
    }

    #[Test]
    public function itShouldFailCreatingAUserNotificationSystemKeyIsWrong(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => NOTIFICATION_TYPE::USER_REGISTERED,
                'system_key' => 'wrong system key',
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The system key is wrong', $responseContent->message);
        $this->assertEquals('The system key is wrong', $responseContent->errors->system_key);
    }

    #[Test]
    public function itShouldFailCreatingAUserNotificationNotAuthorized(): void
    {
        $usersId = [
            self::USER_ID,
            self::USER_2_ID,
            self::USER_3_ID,
        ];
        $client = $this->getNewClientNoAuthenticated();
        $systemKey = $this->getContainer()->getParameter('common.system.key');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'users_id' => $usersId,
                'type' => 'wrong type',
                'system_key' => $systemKey,
                'notification_data' => ['group_name' => 'group name', 'user_name' => 'user name'],
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals($response->getStatusCode(), Response::HTTP_UNAUTHORIZED);
    }
}

<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupUserRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupUserRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/user';
    private const METHOD = 'DELETE';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const GROUP_USERS_TO_REMOVE_ID = [
        '1befdbe2-9c14-42f0-850f-63e061e33b8f',
        '08eda546-739f-4ab7-917a-8a9dbee426ef',
        '6df60afd-f7c3-4c2c-b920-e265f266c560',
    ];

    /** @test */
    public function itShouldRemoveUsersFromTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                // 'group_id' => self::GROUP_ID,
                'group_id' => [self::GROUP_ID],
                'users' => self::GROUP_USERS_TO_REMOVE_ID,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users removed', $responseContent->message);
        $this->assertEquals(self::GROUP_USERS_TO_REMOVE_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldAllowOnly50UsersToRemove(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(
                    array_fill(0, 50, self::GROUP_USERS_TO_REMOVE_ID[0]),
                    [self::GROUP_USERS_TO_REMOVE_ID[1]]
                ),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users removed', $responseContent->message);
        $this->assertEquals([self::GROUP_USERS_TO_REMOVE_ID[0]], $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'users' => self::GROUP_USERS_TO_REMOVE_ID,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'not valid id',
                'users' => self::GROUP_USERS_TO_REMOVE_ID,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailUsersIdIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => null,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->users);
    }

    /** @test */
    public function itShouldFailUsersIdIsNotValid(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::GROUP_USERS_TO_REMOVE_ID, ['id not valid']),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->users);
    }

    /** @test */
    public function itShouldFailUsersIsNotAnArray(): void
    {
        $this->client = $this->getNewClientAuthenticatedAdmin();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::GROUP_USER_ADMIN_ID,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->users);
    }

    /** @test */
    public function itShouldFailCannotRemoveAllAdminUsersFromTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => [self::GROUP_USER_ADMIN_ID],
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_without_admin'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Cannot remove all admins form a group', $responseContent->message);
        $this->assertEquals('Cannot remove all admins form a group', $responseContent->errors->group_without_admin);
    }

    /** @test */
    public function itShouldFailCannotRemoveAllUsersFromTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::GROUP_USERS_TO_REMOVE_ID, [self::GROUP_USER_ADMIN_ID]),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Cannot remove all users form a group', $responseContent->message);
        $this->assertEquals('Cannot remove all users form a group', $responseContent->errors->group_empty);
    }

    /** @test */
    public function itShouldFailGroupOrUsersNotFound(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => '5f911e7c-f839-4ae0-8f5e-109ad169d2b2',
                'users' => array_merge(self::GROUP_USERS_TO_REMOVE_ID, [self::GROUP_USER_ADMIN_ID]),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_users_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group or users not found', $responseContent->message);
        $this->assertEquals('Group or users not found', $responseContent->errors->group_users_not_found);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotAdminOfTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedAdmin();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::GROUP_USERS_TO_REMOVE_ID, [self::GROUP_USER_ADMIN_ID]),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Not permissions in this group', $responseContent->message);
        $this->assertEquals('Not permissions in this group', $responseContent->errors->permissions);
    }
}

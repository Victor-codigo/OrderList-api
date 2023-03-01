<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupUserAdd;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupUserAddControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/user';
    private const METHOD = 'POST';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USERS_100_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_TO_ADD_IDS = [
        '1552b279-5f78-4585-ae1b-31be2faabba8',
        'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
    ];

    private const USERS_DELETED_OR_NOT_ACTIVE = [
        '68e94495-16f0-4acd-adbe-f2b9575e6544', // deleted
        'bd2cbad1-6ccf-48e3-bb92-bc9961bc011e', // not active
    ];
    private const USER_ALREADY_IN_THE_GROUP = '1befdbe2-9c14-42f0-850f-63e061e33b8f';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldAddAllUsersToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users added to the group', $responseContent->message);
        $this->assertSame(self::USER_TO_ADD_IDS, $responseContent->data->id);
    }

    /** @test */
    public function itShouldAddOnlyTwoUsersToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::USER_TO_ADD_IDS, [self::USER_ALREADY_IN_THE_GROUP]),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users added to the group', $responseContent->message);
        $this->assertSame(self::USER_TO_ADD_IDS, $responseContent->data->id);
    }

    /** @test */
    public function itShouldAddAllUsersToTheGroupAdminIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users added to the group', $responseContent->message);
        $this->assertEquals(self::USER_TO_ADD_IDS, (array) $responseContent->data->id);
    }

    /** @test */
    public function itShouldAddAllUsersToTheGroupAdminIsFalse(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => false,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users added to the group', $responseContent->message);
        $this->assertEquals(self::USER_TO_ADD_IDS, (array) $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'users' => array_merge(self::USER_TO_ADD_IDS, ['2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['group_id' => ['not_blank', 'not_null']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'not valid id',
                'users' => array_merge(self::USER_TO_ADD_IDS, ['2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['group_id' => ['uuid_invalid_characters']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => '431a2943-7b3c-4f80-b461-791c78792936',
                'users' => array_merge(self::USER_TO_ADD_IDS, ['2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_NOT_FOUND);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);
        $this->assertSame(['group_not_found' => 'Group not found'], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => null,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['users' => ['not_blank']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersAreNotRegisteredOrActive(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USERS_DELETED_OR_NOT_ACTIVE,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_validation'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong users', $responseContent->message);
        $this->assertSame(['users_validation' => 'Wrong users'], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersIdAreNotValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::USER_TO_ADD_IDS, ['not valid id']),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['users' => ['uuid_invalid_characters']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersAreEmpty(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => [],
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['users' => ['not_blank']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUserIsNotAdminOfTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permission'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Permissions denied', $responseContent->message);
        $this->assertSame(['permission' => 'Permissions denied'], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailGroupUsersNumberExceeded100(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_USERS_100_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_users_exceeded'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group User number exceeded', $responseContent->message);
        $this->assertSame(['group_users_exceeded' => 'Group User number exceeded'], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUnauthorizedUser(): void
    {
        $client = $this->getNewClientAuthenticated('not valid user', 'password');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USER_TO_ADD_IDS,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseContent->code);
    }
}

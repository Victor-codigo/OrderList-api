<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupUserRoleChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupUserRoleChangeControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/user/role';
    private const METHOD = 'PUT';
    private const USER_NOT_GROUP_ADMIN_NAME = 'email.other.active@host.com';
    private const USER_NOT_GROUP_ADMINPASSWORD = '123456';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const USERS_ID = [
        self::GROUP_USER_ADMIN_ID,
        '1befdbe2-9c14-42f0-850f-63e061e33b8f',
        '6df60afd-f7c3-4c2c-b920-e265f266c560',
     ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldChangeGroupUsersRol(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USERS_ID,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users roles has been changed', $responseContent->message);

        foreach (self::USERS_ID as $userId) {
            $this->assertContains($userId, (array) $responseContent->data->id);
        }
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
                'users' => ['bd2cbad1-6ccf-48e3-bb92-bc9961bc011e'],
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users roles has been changed', $responseContent->message);
        $this->assertEmpty($responseContent->data->id);
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
                'users' => self::USERS_ID,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['group_id' => ['not_blank', 'not_null']], (array) $responseContent->errors);
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
                'users' => self::USERS_ID,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['group_id' => ['uuid_invalid_characters']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailGroupUserIdIsNotGroupAdmin(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_NOT_GROUP_ADMIN_NAME, self::USER_NOT_GROUP_ADMINPASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => self::USERS_ID,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permission'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Permissions denied', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGroupIdIsRegistered(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'caa8b54a-eb5e-4134-8ae2-a3946a428ec7',
                'users' => self::USERS_ID,
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_not_found'], Response::HTTP_NOT_FOUND);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Users do not exist in the group', $responseContent->message);
    }

    /** @test */
    public function itShouldFailUsersIdIsNull(): void
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
        $this->assertEquals(['users' => ['not_blank']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailNotAllUsersIdAreValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::USERS_ID, ['not valid id']),
                'admin' => true,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['users' => ['uuid_invalid_characters']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailGroupShouldHaveAtLeastOneAdmin(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'users' => array_merge(self::USERS_ID, [self::GROUP_USER_ADMIN_ID]),
                'admin' => false,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_admins'], Response::HTTP_CONFLICT);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('It should be at least one admin in the group', $responseContent->message);
        $this->assertArrayHasKey('group_not_admins', (array) $responseContent->errors);
    }
}

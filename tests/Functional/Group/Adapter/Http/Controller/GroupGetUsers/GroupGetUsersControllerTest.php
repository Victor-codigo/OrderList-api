<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetUsers;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupGetUsersControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/user/{group_id}?limit={limit}&offset={offset}';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_EMAIL = 'email.already.active@host.com';
    private const USER_PASSWORD = '123456';

    /** @test */
    public function itShouldGetTheGroupUsersData(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 50, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 49), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoLimitSet(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', 'limit={limit}&', '{offset}'],
                [self::GROUP_ID, '', 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 49), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoOffsetSet(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '&offset={offset}'],
                [self::GROUP_ID, 50, ''],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 49), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoLimitAndNoOffsetSet(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '?limit={limit}', '&offset={offset}'],
                [self::GROUP_ID, '', ''],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 49), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
        }
    }

    /** @test */
    public function itShouldGet5Users(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 5, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 4), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(5, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
        }
    }

    /** @test */
    public function itShouldGet3UsersWithOffset3(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 4, 0],
                self::ENDPOINT
            )
        );

        $response10Users = $this->client->getResponse();
        $responseContent10Users = json_decode($response10Users->getContent());

        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 3, 1],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, range(0, 2), [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(3, $responseContent->data);

        foreach ($responseContent->data as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertContainsEquals($userData, $responseContent10Users->data);
        }
    }

    /** @test */
    public function itShouldFailGroupIdIsEmpty(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                ['', 50, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                ['not_valid_id', 50, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotRegistered(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                ['20354d7a-e4fe-47af-8ff6-187bca92f3f9', 50, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);

        $this->assertEquals('Group not found', $responseContent->errors->group_not_found);
    }

    /** @test */
    public function itShouldFailLimitTooHigh(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 51, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['limit'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['less_than_or_equal'], $responseContent->errors->limit);
    }

    /** @test */
    public function itShouldFailLimitTooLow(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, -1, 0],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['limit'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than_or_equal'], $responseContent->errors->limit);
    }

    /** @test */
    public function itShouldFailOffsetTooLow(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 1, -1],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['offset'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than_or_equal'], $responseContent->errors->offset);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotInTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedAdmin();
        $this->client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '{limit}', '{offset}'],
                [self::GROUP_ID, 1, 1],
                self::ENDPOINT
            )
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }
}

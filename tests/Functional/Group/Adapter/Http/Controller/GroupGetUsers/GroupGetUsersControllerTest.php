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

    private const ENDPOINT = '/api/v1/groups/user/{group_id}?page={page}&page_items={page_items}';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_EMAIL = 'email.already.active@host.com';
    private const USER_PASSWORD = '123456';

    /** @test */
    public function itShouldGetTheGroupUsersData(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 50],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(2, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoLimitSet(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '&page_items={page_items}'],
                [self::GROUP_ID, 1, ''],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(2, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoOffsetSet(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  'page={page}', '{page_items}'],
                [self::GROUP_ID, '', 50],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(2, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
        }
    }

    /** @test */
    public function itShouldGet50UsersNoLimitAndNoOffsetSet(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}', '?page={page}', '&page_items={page_items}'],
                [self::GROUP_ID, '', ''],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(50, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(2, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
        }
    }

    /** @test */
    public function itShouldGet5Users(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 5],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(5, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(20, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
        }
    }

    /** @test */
    public function itShouldGet3UsersWithOffset3(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 4],
                self::ENDPOINT
            )
        );

        $response10Users = $client->getResponse();
        $responseContent10Users = json_decode($response10Users->getContent());

        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 3],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertCount(3, $responseContent->data->users);
        $this->assertEquals(1, $responseContent->data->page);
        $this->assertEquals(34, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
            $this->assertContainsEquals($userData, $responseContent10Users->data->users);
        }
    }

    /** @test */
    public function itShouldFailGroupIdIsEmpty(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                ['', 1, 50],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                ['not_valid_id', 1, 50],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotRegistered(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                ['20354d7a-e4fe-47af-8ff6-187bca92f3f9', 1, 50],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);

        $this->assertEquals('Group not found', $responseContent->errors->group_not_found);
    }

    /** @test */
    public function itShouldFailLimitTooHigh(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 101],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['less_than_or_equal'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailLimitTooLow(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, -1],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailOffsetTooLow(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, -1, 1],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotInTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: str_replace(
                ['{group_id}',  '{page}', '{page_items}'],
                [self::GROUP_ID, 1, 1],
                self::ENDPOINT
            )
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }
}

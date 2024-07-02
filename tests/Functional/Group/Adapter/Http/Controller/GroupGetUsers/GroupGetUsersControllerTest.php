<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetUsers;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupGetUsersControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/user';
    private const string METHOD = 'GET';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string USER_EMAIL = 'email.already.active@host.com';
    private const string USER_PASSWORD = '123456';

    private function assertUsersAreOk(object $responseContent, int $expectedUsersCount, int $expectedPage, int $expectedPagesTotal): void
    {
        $this->assertCount($expectedUsersCount, $responseContent->data->users);
        $this->assertEquals($expectedPage, $responseContent->data->page);
        $this->assertEquals($expectedPagesTotal, $responseContent->data->pages_total);

        foreach ($responseContent->data->users as $userData) {
            $this->assertTrue(property_exists($userData, 'id'));
            $this->assertTrue(property_exists($userData, 'name'));
            $this->assertTrue(property_exists($userData, 'image'));
            $this->assertTrue(property_exists($userData, 'admin'));
            $this->assertTrue(property_exists($userData, 'created_on'));
        }
    }

    /** @test */
    public function itShouldGetTheGroupUsersData(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, $pageItems, $page, 2);
    }

    /** @test */
    public function itShouldGet50UsersNoLimitSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, $pageItems, $page, 2);
    }

    /** @test */
    public function itShouldGet50UsersNoPageSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, $pageItems, $page, 2);
    }

    /** @test */
    public function itShouldGet50UsersNoLimitAndNoOffsetSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, $pageItems, $page, 2);
    }

    /** @test */
    public function itShouldGet5Users(): void
    {
        $page = 1;
        $pageItems = 5;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, $pageItems, $page, 20);
    }

    /** @test */
    public function itShouldGetUserByNameFilterEquals(): void
    {
        $page = 1;
        $pageItems = 5;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'Ona Kilback';
        $orderAsc = true;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, 1, $page, 20);
    }

    /** @test */
    public function itShouldGetUserByNameFilterStartsWith(): void
    {
        $page = 1;
        $pageItems = 5;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $filterValue = 'Ona';
        $orderAsc = true;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, 1, $page, 20);
    }

    /** @test */
    public function itShouldGetUserByNameFilterEndsWith(): void
    {
        $page = 1;
        $pageItems = 5;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::ENDS_WITH->value;
        $filterValue = 'Kilback';
        $orderAsc = true;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, 1, $page, 20);
    }

    /** @test */
    public function itShouldGetUserByNameFilterContains(): void
    {
        $page = 1;
        $pageItems = 5;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::CONTAINS->value;
        $filterValue = 'Kil';
        $orderAsc = true;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'users'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users of the group', $responseContent->message);

        $this->assertUsersAreOk($responseContent, 1, $page, 20);
    }

    /** @test */
    public function itShouldNotGetUserFilteredByNameUserNotExists(): void
    {
        $page = 1;
        $pageItems = 5;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'not exists user';
        $orderAsc = true;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
                ."&order_asc={$orderAsc}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);

        $this->assertEquals('Group not found', $responseContent->errors->group_not_found);
    }

    /** @test */
    public function itShouldFailGroupIdIsEmpty(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $page = 1;
        $pageItems = 50;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id=not_valid_id'
                ."&page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailLimitTooHigh(): void
    {
        $page = 1;
        $pageItems = 101;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."?page={$page}"
                ."&page_items={$pageItems}"
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
        $page = 1;
        $pageItems = -1;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."?page={$page}"
                ."&page_items={$pageItems}"
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
        $page = -1;
        $pageItems = 1;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailFilterSectionNotSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $filterValue = 'user name';
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_text={$filterText}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_null'], $responseContent->errors->filter_section_and_text_not_empty);
    }

    /** @test */
    public function itShouldFailFilterTextNotSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterValue = 'user name';
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."?page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_value={$filterValue}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_null'], $responseContent->errors->filter_section_and_text_not_empty);
    }

    /** @test */
    public function itShouldFailFilterValueNotSet(): void
    {
        $page = 1;
        $pageItems = 50;
        $filterSection = FILTER_SECTION::GROUP_USERS->value;
        $filterText = FILTER_STRING_COMPARISON::EQUALS->value;
        $client = $this->getNewClientAuthenticated(self::USER_EMAIL, self::USER_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
                ."&filter_section={$filterSection}"
                ."&filter_text={$filterText}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['section_filter_value', 'text_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->section_filter_value);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->text_filter_value);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotInTheGroup(): void
    {
        $page = 1;
        $pageItems = 1;
        $client = $this->getNewClientAuthenticatedAdmin();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?group_id='.self::GROUP_ID
                ."&page={$page}"
                ."&page_items={$pageItems}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }
}

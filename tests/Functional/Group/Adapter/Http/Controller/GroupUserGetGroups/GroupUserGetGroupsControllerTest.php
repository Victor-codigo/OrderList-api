<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupUserGetGroups;

use DateTime;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupUserGetGroupsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/user-groups';
    private const string METHOD = 'GET';
    private const string GROUP_USER_ONLY_GROUP_EMAIL = 'email.other_5.active@host.com';
    private const string GROUP_USER_ONLY_GROUP_PASSWORD = '123456';

    /**
     * @return Group[]
     */
    private function getGroupsData(DateTime $createdOn): array
    {
        return [
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users', null),
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'Group100Users', GROUP_TYPE::GROUP, 'This group contains 100 users', null),
            Group::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user', 'image_of_group_type_user'),
            Group::fromPrimitives('e05b2466-9528-4815-ac7f-663c1d89ab55', 'GroupThree', GROUP_TYPE::GROUP, 'This is a group of users', 'image_of_group_type_group'),
            Group::fromPrimitives('78b96ac1-ffcc-458b-8f48-b40c6e65261f', 'GroupFour', GROUP_TYPE::GROUP, 'This is a group of users', 'image_of_group_type_group'),
        ];
    }

    private function assertResponseDataIsOk(object $responseContent, array $groupsId, array $groupsType, array $groupsName, array $groupsDescription): void
    {
        $this->assertTrue(property_exists($responseContent->data, 'page'));
        $this->assertTrue(property_exists($responseContent->data, 'pages_total'));
        $this->assertTrue(property_exists($responseContent->data, 'groups'));
        $this->assertCount(count($groupsId), $responseContent->data->groups);

        foreach ($responseContent->data->groups as $groupData) {
            $this->assertGroupDataIsOk($groupData, $groupsId, $groupsType, $groupsName, $groupsDescription);
        }
    }

    private function assertGroupDataIsOk(object $groupData, array $groupsId, array $groupsType, array $groupsName, array $groupsDescription): void
    {
        $this->assertTrue(property_exists($groupData, 'group_id'));
        $this->assertTrue(property_exists($groupData, 'type'));
        $this->assertTrue(property_exists($groupData, 'name'));
        $this->assertTrue(property_exists($groupData, 'description'));
        $this->assertTrue(property_exists($groupData, 'created_on'));
        $this->assertTrue(property_exists($groupData, 'admin'));
        $this->assertContains($groupData->group_id, $groupsId);
        $this->assertContains(
            'group' === $groupData->type
                ? GROUP_TYPE::GROUP
                : GROUP_TYPE::USER,
            $groupsType
        );
        $this->assertContains($groupData->name, $groupsName);
        $this->assertContains($groupData->description, $groupsDescription);
        $this->assertIsString($groupData->created_on);
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $groupData->created_on);
        $this->assertIsBool($groupData->admin);
    }

    /** @test */
    public function itShouldGetAllGroupsDataForTheUser(): void
    {
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsType = array_map(fn (Group $group) => $group->getType()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=5'
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndGroupNameEquals(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterText = FILTER_STRING_COMPARISON::EQUALS;
        $filterValue = 'GroupOne';
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = [$groups[0]->getId()->getValue()];
        $groupsType = array_map(fn (Group $group) => $group->getType()->getValue(), $groups);
        $groupsName = [$groups[0]->getName()->getValue()];
        $groupsDescription = [$groups[0]->getDescription()->getValue()];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndGroupNameStartsWith(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;
        $filterValue = 'Group';
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsType = array_map(fn (Group $group) => $group->getType()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=5'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndGroupNameEndsWith(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterText = FILTER_STRING_COMPARISON::ENDS_WITH;
        $filterValue = 'One';
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = [$groups[0]->getId()->getValue()];
        $groupsType = [$groups[0]->getType()->getValue()];
        $groupsName = [$groups[0]->getName()->getValue()];
        $groupsDescription = [$groups[0]->getDescription()->getValue()];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndGroupNameContains(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterText = FILTER_STRING_COMPARISON::CONTAINS;
        $filterValue = 'oup';
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsType = array_map(fn (Group $group) => $group->getType()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=5'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserGroupTypeUser(): void
    {
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = [$groups[2]->getId()->getValue()];
        $groupsType = [$groups[2]->getType()->getValue()];
        $groupsName = [$groups[2]->getName()->getValue()];
        $groupsDescription = [$groups[2]->getDescription()->getValue()];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                .'&group_type=user'
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndSectionFilterNotGroup(): void
    {
        $filterSection = FILTER_SECTION::LIST_ORDERS;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;
        $filterValue = 'Group';
        $groupCreatedOn = new DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsType = array_map(fn (Group $group) => $group->getType()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=5'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

        $this->assertResponseDataIsOk($responseContent, $groupsId, $groupsType, $groupsName, $groupsDescription);
    }

    /** @test */
    public function itShouldFailUserHasNoGroups(): void
    {
        $client = $this->getNewClientAuthenticated(self::GROUP_USER_ONLY_GROUP_EMAIL, self::GROUP_USER_ONLY_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                .'&group_type=group'
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGroupsDataForTheUserAndFilterSectionIsNull(): void
    {
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;
        $filterValue = 'Group';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Errors', $responseContent->message);

        $this->assertEquals(['not_null'], $responseContent->errors->filter_section_and_text_not_empty);
    }

    /** @test */
    public function itShouldFailGroupsDataForTheUserAndFilterTextIsNull(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterValue = 'Group';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                ."&filter_section={$filterSection->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['filter_section_and_text_not_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Errors', $responseContent->message);

        $this->assertEquals(['not_null'], $responseContent->errors->filter_section_and_text_not_empty);
    }

    /** @test */
    public function itShouldFailGroupsDataForTheUserAndFilterValueIsNull(): void
    {
        $filterSection = FILTER_SECTION::GROUP;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=2'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['section_filter_value', 'text_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Errors', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->section_filter_value);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->text_filter_value);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndFilterPageIsWrong(): void
    {
        $filterSection = FILTER_SECTION::LIST_ORDERS;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;
        $filterValue = 'Group';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=-1'
                .'&page_items=2'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Errors', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldGroupsDataForTheUserAndFilterPageItemsIsWrong(): void
    {
        $filterSection = FILTER_SECTION::LIST_ORDERS;
        $filterText = FILTER_STRING_COMPARISON::STARTS_WITH;
        $filterValue = 'Group';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'?page=1'
                .'&page_items=-2'
                ."&filter_section={$filterSection->value}"
                ."&filter_text={$filterText->value}"
                ."&filter_value={$filterValue}"
                .'&order_asc=true'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Errors', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }
}

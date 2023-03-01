<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/data/';
    private const METHOD = 'GET';
    private const GROUP_USER_ONLY_GROUP_EMAIL = 'email.other.active@host.com';
    private const GROUP_USER_ONLY_GROUP_PASSWORD = '123456';

    private function getGroupsData(\DateTime $createdOn): array
    {
        return [
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users'),
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'Group100Users', GROUP_TYPE::GROUP, 'This group contains 100 users'),
        ];
    }

    private function getGroupsDoNotExitsData(): array
    {
        return [
            Group::fromPrimitives('b9e452d5-29ab-44af-aa97-79169bdd5899', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users'),
            Group::fromPrimitives('24f2c226-6d04-41bc-810e-a24be4382633', 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user'),
        ];
    }

    /** @test */
    public function itShouldGetGroupsData(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groupsId)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups data', $responseContent->message);

        $this->assertCount(count($groupsId), $responseContent->data);

        foreach ($responseContent->data as $groupData) {
            $this->assertTrue(property_exists($groupData, 'group_id'));
            $this->assertTrue(property_exists($groupData, 'name'));
            $this->assertTrue(property_exists($groupData, 'description'));
            $this->assertTrue(property_exists($groupData, 'created_on'));
            $this->assertContains($groupData->group_id, $groupsId);
            $this->assertContains($groupData->name, $groupsName);
            $this->assertContains($groupData->description, $groupsDescription);
            $this->assertIsString($groupData->created_on);
            $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $groupData->created_on);
        }
    }

    /** @test */
    public function itShouldGetGroupsDataFor50Groups(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);
        $groups50Id = array_merge(array_fill(0, 47, $groupsId[0]), $groupsId);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groups50Id)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups data', $responseContent->message);

        $this->assertCount(count($groupsId), $responseContent->data);

        foreach ($responseContent->data as $groupData) {
            $this->assertTrue(property_exists($groupData, 'group_id'));
            $this->assertTrue(property_exists($groupData, 'name'));
            $this->assertTrue(property_exists($groupData, 'description'));
            $this->assertTrue(property_exists($groupData, 'created_on'));
            $this->assertContains($groupData->group_id, $groupsId);
            $this->assertContains($groupData->name, $groupsName);
            $this->assertContains($groupData->description, $groupsDescription);
            $this->assertIsString($groupData->created_on);
            $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $groupData->created_on);
        }
    }

    /** @test */
    public function itShouldStripGroupsIdThatExceedTheMaximum(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);
        $groups50Id = array_merge(array_fill(0, 48, $groupsId[0]), $groupsId);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groups50Id)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups data', $responseContent->message);

        $this->assertCount(2, $responseContent->data);

        foreach ($responseContent->data as $groupData) {
            $this->assertTrue(property_exists($groupData, 'group_id'));
            $this->assertTrue(property_exists($groupData, 'name'));
            $this->assertTrue(property_exists($groupData, 'description'));
            $this->assertTrue(property_exists($groupData, 'created_on'));
            $this->assertContains($groupData->group_id, $groupsId);
            $this->assertContains($groupData->name, $groupsName);
            $this->assertContains($groupData->description, $groupsDescription);
            $this->assertIsString($groupData->created_on);
            $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $groupData->created_on);
        }
    }

    /** @test */
    public function itShouldReturnOnlyTheGroupThatTheUserSessionBelongTo(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groupsId)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups data', $responseContent->message);

        $this->assertCount(1, $responseContent->data);

        foreach ($responseContent->data as $groupData) {
            $this->assertTrue(property_exists($groupData, 'group_id'));
            $this->assertTrue(property_exists($groupData, 'name'));
            $this->assertTrue(property_exists($groupData, 'description'));
            $this->assertTrue(property_exists($groupData, 'created_on'));
            $this->assertContains($groupData->group_id, $groupsId);
            $this->assertContains($groupData->name, $groupsName);
            $this->assertContains($groupData->description, $groupsDescription);
            $this->assertIsString($groupData->created_on);
            $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $groupData->created_on);
        }
    }

    /** @test */
    public function itShouldFailNotGroupsIdProvided(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_NOT_FOUND);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Not found: error 404', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGroupsIdNotValid(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue().'-', $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groupsId)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_too_long'], $responseContent->errors->groups_id);
    }

    /** @test */
    public function itShouldFailUserSessionDoesNotBelongsToAGroup(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);

        $client = $this->getNewClientAuthenticated(self::GROUP_USER_ONLY_GROUP_EMAIL, self::GROUP_USER_ONLY_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.$groupsId[0].','.$groupsId[1]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGroupsIdsAreNotRegisteredGroupsId(): void
    {
        $groups = $this->getGroupsDoNotExitsData();
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.implode(',', $groupsId)
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
    }
}

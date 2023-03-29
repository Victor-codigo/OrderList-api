<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupUserGetGroups;

use Common\Domain\Response\RESPONSE_STATUS;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupUserGetGroupsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/user-groups';
    private const METHOD = 'GET';
    private const GROUP_USER_ONLY_GROUP_EMAIL = 'email.other.active@host.com';
    private const GROUP_USER_ONLY_GROUP_PASSWORD = '123456';

    private function getGroupsData(\DateTime $createdOn): array
    {
        return [
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users', null),
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'Group100Users', GROUP_TYPE::GROUP, 'This group contains 100 users', null),
        ];
    }

    /** @test */
    public function itShouldAllGroupsDataForTheUser(): void
    {
        $groupCreatedOn = new \DateTime();
        $groups = $this->getGroupsData($groupCreatedOn);
        $groupsId = array_map(fn (Group $group) => $group->getId()->getValue(), $groups);
        $groupsName = array_map(fn (Group $group) => $group->getName()->getValue(), $groups);
        $groupsDescription = array_map(fn (Group $group) => $group->getDescription()->getValue(), $groups);

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'?page=1&page_items=2'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Groups of the user', $responseContent->message);

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
    public function itShouldFailUserHasNoGroups(): void
    {
        $client = $this->getNewClientAuthenticated(self::GROUP_USER_ONLY_GROUP_EMAIL, self::GROUP_USER_ONLY_GROUP_PASSWORD);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('No groups found', $responseContent->message);
    }
}

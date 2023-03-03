<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupUserGetGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsDto;
use Group\Domain\Service\GroupUserGetGroups\GroupUserGetGroupsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

class GroupUserGetGroupsServiceTest extends TestCase
{
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const PATH_TO_GROUP_IMAGE_PUBLIC_PATH = 'path/to/group/public/image/path';

    private GroupUserGetGroupsService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private GroupGetDataService $groupGetDataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->groupGetDataService = new GroupGetDataService($this->groupRepository, self::PATH_TO_GROUP_IMAGE_PUBLIC_PATH);
        $this->object = new GroupUserGetGroupsService($this->userGroupRepository, $this->groupGetDataService);
    }

    private function getUserId(): Identifier
    {
        return ValueObjectFactory::createIdentifier(self::USER_ID);
    }

    /**
     * @return UserGroup[]
     */
    private function getUserGroups(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', self::USER_ID, [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', self::USER_ID, [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', self::USER_ID, [GROUP_ROLES::ADMIN], $group),
        ];
    }

    /**
     * @return Group[]
     */
    private function getGroupsData(): array
    {
        return
        [
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'Group100Users', GROUP_TYPE::GROUP, 'This group contains 100 users', '2023-02-23 17:18:03'),
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users', '2023-02-23 17:18:03'),
        ];
    }

    private function mockGroupGetDataService(array $groupsId, array $expectedGroupsData, InvocationOrder $timesInvokedFindGroupsByIdOrFail): void
    {
        $this->groupRepository
            ->expects($timesInvokedFindGroupsByIdOrFail)
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($expectedGroupsData);
    }

    /** @test */
    public function itShouldGetUserGroupsAllData(): void
    {
        $userId = $this->getUserId();
        $expectedUserGroups = $this->getUserGroups();
        $expectedGroupsData = $this->getGroupsData();
        $groupGetDataDto = new GroupGetDataDto(array_map(
            fn (UserGroup $userGroup) => $userGroup->getGroupId(),
            $expectedUserGroups
        ));

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($userId)
            ->willReturn($expectedUserGroups);

        $this->mockGroupGetDataService($groupGetDataDto->groupsId, $expectedGroupsData, $this->once());

        $return = $this->object->__invoke(new GroupUserGetGroupsDto($userId));
        $returnData = iterator_to_array($return);

        $this->assertCount(count($expectedGroupsData), $returnData);

        foreach ($returnData as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);

            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);
        }
    }

    /** @test */
    public function itShouldFailNoGroupsFound(): void
    {
        $userId = $this->getUserId();
        $expectedUserGroups = $this->getUserGroups();
        $expectedGroupsData = $this->getGroupsData();
        $groupGetDataDto = new GroupGetDataDto(array_map(
            fn (UserGroup $userGroup) => $userGroup->getGroupId(),
            $expectedUserGroups
        ));

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($userId)
            ->willThrowException(new DBNotFoundException());

        $this->mockGroupGetDataService($groupGetDataDto->groupsId, $expectedGroupsData, $this->never());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke(new GroupUserGetGroupsDto($userId));
    }
}

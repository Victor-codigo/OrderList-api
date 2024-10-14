<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupRemoveAllUserGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsOutputDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\GroupRemoveUserFromGroupsService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveUserFromGroupsServiceTest extends TestCase
{
    private const string USER_ID = 'b23f12cf-75cb-402e-b771-77fba3b0875a';

    private GroupRemoveUserFromGroupsService $object;
    private MockObject&GroupRepositoryInterface $groupRepository;
    private MockObject&UserGroupRepositoryInterface $userGroupRepository;
    /**
     * @var MockObject&PaginatorInterface<int, Group>
     */
    private MockObject&PaginatorInterface $groupsUsersNumPaginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->groupsUsersNumPaginator = $this->createMock(PaginatorInterface::class);

        $this->object = new GroupRemoveUserFromGroupsService(
            $this->groupRepository,
            $this->userGroupRepository
        );
    }

    /**
     * @return UserGroup[]
     */
    private function getUserGroups(): array
    {
        /** @var MockObject&Group $group */
        $group = $this->createMock(Group::class);

        return [
            '594115a9-1f01-43f2-918d-da527e302c81' => UserGroup::fromPrimitives(
                '594115a9-1f01-43f2-918d-da527e302c81',
                self::USER_ID,
                [GROUP_ROLES::ADMIN],
                $group
            ),
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => UserGroup::fromPrimitives(
                '10545a18-14ef-4218-baa7-8ad9f7b9a921',
                self::USER_ID,
                [GROUP_ROLES::ADMIN],
                $group
            ),
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => UserGroup::fromPrimitives(
                '4ec0985d-4a9d-493d-9fb1-61bab6c4d730',
                self::USER_ID,
                [GROUP_ROLES::ADMIN],
                $group
            ),
            '674cc177-935b-4494-9480-b66a3d9458a4' => UserGroup::fromPrimitives(
                '674cc177-935b-4494-9480-b66a3d9458a4',
                self::USER_ID,
                [GROUP_ROLES::USER],
                $group
            ),
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => UserGroup::fromPrimitives(
                'f787ac40-e688-4a23-a3de-278002b2ffa6',
                self::USER_ID,
                [GROUP_ROLES::ADMIN],
                $group
            ),
        ];
    }

    /**
     * @return UserGroup[]
     */
    private function getUserGroupsAllSameRole(GROUP_ROLES $groupRole): array
    {
        $usersGroups = $this->getUserGroups();
        $adminRole = ValueObjectFactory::createRoles(
            [ValueObjectFactory::createRol($groupRole)]
        );

        return array_map(
            fn (UserGroup $userGroup): UserGroup => $userGroup->setRoles($adminRole),
            $usersGroups
        );
    }

    /**
     * @return Group[]
     */
    private function getGroupsOfUsersGroups(): array
    {
        return [
            '594115a9-1f01-43f2-918d-da527e302c81' => Group::fromPrimitives(
                '594115a9-1f01-43f2-918d-da527e302c81',
                'Group name 1',
                GROUP_TYPE::USER,
                'Group description 1',
                null
            ),
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => Group::fromPrimitives(
                '10545a18-14ef-4218-baa7-8ad9f7b9a921',
                'Group name 2',
                GROUP_TYPE::GROUP,
                'Group description 2',
                null
            ),
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => Group::fromPrimitives(
                '4ec0985d-4a9d-493d-9fb1-61bab6c4d730',
                'Group name 3',
                GROUP_TYPE::GROUP,
                'Group description 3',
                null
            ),
            '674cc177-935b-4494-9480-b66a3d9458a4' => Group::fromPrimitives(
                '674cc177-935b-4494-9480-b66a3d9458a4',
                'Group name 4',
                GROUP_TYPE::GROUP,
                'Group description 4',
                null
            ),
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => Group::fromPrimitives(
                'f787ac40-e688-4a23-a3de-278002b2ffa6',
                'Group name 5',
                GROUP_TYPE::GROUP,
                'Group description 5',
                null
            ),
        ];
    }

    /**
     * @param UserGroup[] $usersGroups
     *
     * @return Identifier[]
     */
    private function getGroupsIdFromUsersGroups(array $usersGroups): array
    {
        return array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
            $usersGroups
        );
    }

    /**
     * @return array<int, array{ groupId: string, groupUsers: int }>
     */
    private function getGroupsUsersNumber(): array
    {
        return [
            [
                'groupId' => '594115a9-1f01-43f2-918d-da527e302c81',
                'groupUsers' => 1,
            ],
            [
                'groupId' => '10545a18-14ef-4218-baa7-8ad9f7b9a921',
                'groupUsers' => 10,
            ],
            [
                'groupId' => '4ec0985d-4a9d-493d-9fb1-61bab6c4d730',
                'groupUsers' => 20,
            ],
            [
                'groupId' => '674cc177-935b-4494-9480-b66a3d9458a4',
                'groupUsers' => 30,
            ],
            [
                'groupId' => 'f787ac40-e688-4a23-a3de-278002b2ffa6',
                'groupUsers' => 1,
            ],
        ];
    }

    /**
     * @return array<array{ groupId: string, groupUsers: int }>
     */
    private function getGroupsUsersNumberAllSame(int $numUsers): array
    {
        $groupUsersNumbers = $this->getGroupsUsersNumber();

        return array_map(
            fn (array $groupUsersNumber): array => [
                'groupId' => $groupUsersNumber['groupId'],
                'groupUsers' => $numUsers,
            ],
            $groupUsersNumbers
        );
    }

    /**
     * @return Identifier[]
     */
    private function getGroupsIdToChangeAdmin(): array
    {
        return [
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => ValueObjectFactory::createIdentifier('10545a18-14ef-4218-baa7-8ad9f7b9a921'),
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => ValueObjectFactory::createIdentifier('4ec0985d-4a9d-493d-9fb1-61bab6c4d730'),
        ];
    }

    /**
     * @return UserGroup[]
     */
    private function getUsersGroupsToSetAdmin(): array
    {
        /** @var MockObject&Group $group */
        $group = $this->createMock(Group::class);

        return [
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => new UserGroup(
                ValueObjectFactory::createIdentifier('10545a18-14ef-4218-baa7-8ad9f7b9a921'),
                ValueObjectFactory::createIdentifier('f8509235-4bea-43e7-81e5-2943a0629b38'),
                ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::USER)]),
                $group
            ),
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => new UserGroup(
                ValueObjectFactory::createIdentifier('4ec0985d-4a9d-493d-9fb1-61bab6c4d730'),
                ValueObjectFactory::createIdentifier('1535c5ff-b816-4cf6-8b2a-1379ac08450b'),
                ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::USER)]),
                $group
            ),
        ];
    }

    /**
     * @param Group[]     $groupsToRemoveExpected
     * @param UserGroup[] $usersGroupsToRemove
     * @param UserGroup[] $usersGroupsToSetAsAdmin
     */
    private function assertReturnIdOk(array $groupsToRemoveExpected, array $usersGroupsToRemove, array $usersGroupsToSetAsAdmin, GroupRemoveAllUserGroupsOutputDto $usersGroupsRemoveOutput): void
    {
        $this->assertObjectHasProperty('groupsIdRemoved', $usersGroupsRemoveOutput);
        $this->assertObjectHasProperty('usersIdGroupsRemoved', $usersGroupsRemoveOutput);
        $this->assertObjectHasProperty('usersGroupsIdSetAsAdmin', $usersGroupsRemoveOutput);
        $this->assertEquals($groupsToRemoveExpected, $usersGroupsRemoveOutput->groupsIdRemoved);
        $this->assertEquals($usersGroupsToRemove, $usersGroupsRemoveOutput->usersIdGroupsRemoved);
        $this->assertEquals($usersGroupsToSetAsAdmin, $usersGroupsRemoveOutput->usersGroupsIdSetAsAdmin);
    }

    #[Test]
    public function itShouldRemoveUserFromGroups(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumber();
        $groupsIdToChangeAdmin = $this->getGroupsIdToChangeAdmin();
        $usersGroupsToSetAsAdmin = $this->getUsersGroupsToSetAdmin();
        $groupsToRemoveExpected = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];
        $usersGroupsToRemove = [
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => $usersGroups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => $usersGroups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            '674cc177-935b-4494-9480-b66a3d9458a4' => $usersGroups['674cc177-935b-4494-9480-b66a3d9458a4'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsFirstUserByRolOrFail')
            ->with($groupsIdToChangeAdmin, GROUP_ROLES::USER)
            ->willReturn($usersGroupsToSetAsAdmin);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroupsToRemove);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($usersGroupsToSetAsAdmin);

        $return = $this->object->__invoke($groups, $usersGroups);

        $this->assertReturnIdOk($groupsToRemoveExpected, array_values($usersGroupsToRemove), $usersGroupsToSetAsAdmin, $return);
    }

    #[Test]
    public function itShouldNotRemoveGroupsNoGroupsPassed(): void
    {
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with([])
            ->willThrowException(new DBNotFoundException());

        $this->groupsUsersNumPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->groupRepository
            ->expects($this->never())
            ->method('remove');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupsFirstUserByRolOrFail');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $return = $this->object->__invoke([], []);

        $this->assertReturnIdOk([], [], [], $return);
    }

    #[Test]
    public function itShouldRemoveOnlyGroupsNotUsers(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroupsAllSameRole(GROUP_ROLES::ADMIN);
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumberAllSame(1);
        $groupsIdToChangeAdmin = [];
        $usersGroupsToSetAsAdmin = [];
        $groupsToRemoveExpected = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => $groups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => $groups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            '674cc177-935b-4494-9480-b66a3d9458a4' => $groups['674cc177-935b-4494-9480-b66a3d9458a4'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];
        $usersGroupsToRemove = [];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsFirstUserByRolOrFail')
            ->with($groupsIdToChangeAdmin, GROUP_ROLES::USER)
            ->willReturn($usersGroupsToSetAsAdmin);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroupsToRemove);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($usersGroupsToSetAsAdmin);

        $return = $this->object->__invoke($groups, $usersGroups);

        $this->assertReturnIdOk($groupsToRemoveExpected, array_values($usersGroupsToRemove), $usersGroupsToSetAsAdmin, $return);
    }

    #[Test]
    public function itShouldRemoveOnlyUsersNotGroups(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroupsAllSameRole(GROUP_ROLES::USER);
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumberAllSame(10);
        $groupsIdToChangeAdmin = [];
        $usersGroupsToSetAsAdmin = [];
        $groupsToRemoveExpected = [];
        $usersGroupsToRemove = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $usersGroups['594115a9-1f01-43f2-918d-da527e302c81'],
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => $usersGroups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => $usersGroups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            '674cc177-935b-4494-9480-b66a3d9458a4' => $usersGroups['674cc177-935b-4494-9480-b66a3d9458a4'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $usersGroups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsFirstUserByRolOrFail')
            ->with($groupsIdToChangeAdmin, GROUP_ROLES::USER)
            ->willReturn($usersGroupsToSetAsAdmin);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroupsToRemove);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($usersGroupsToSetAsAdmin);

        $return = $this->object->__invoke($groups, $usersGroups);

        $this->assertReturnIdOk($groupsToRemoveExpected, array_values($usersGroupsToRemove), $usersGroupsToSetAsAdmin, $return);
    }

    #[Test]
    public function itShouldFailGroupUsersNumberNotFound(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->groupsUsersNumPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->groupRepository
            ->expects($this->never())
            ->method('remove');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupsFirstUserByRolOrFail');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($groups, $usersGroups);
    }

    #[Test]
    public function itShouldFailRemoveGroupsException(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumber();
        $groupsToRemoveExpected = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected)
            ->willThrowException(new DBConnectionException());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupsFirstUserByRolOrFail');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($groups, $usersGroups);
    }

    #[Test]
    public function itShouldFailRemoveUsersException(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumber();
        $groupsToRemoveExpected = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];
        $usersGroupsToRemove = [
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => $usersGroups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => $usersGroups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            '674cc177-935b-4494-9480-b66a3d9458a4' => $usersGroups['674cc177-935b-4494-9480-b66a3d9458a4'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupsFirstUserByRolOrFail');

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroupsToRemove)
            ->willThrowException(new DBConnectionException());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($groups, $usersGroups);
    }

    #[Test]
    public function itShouldNotChangeUsersToAdminUsersNotFound(): void
    {
        $groups = $this->getGroupsOfUsersGroups();
        $usersGroups = $this->getUserGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumber();
        $groupsIdToChangeAdmin = $this->getGroupsIdToChangeAdmin();
        $groupsToRemoveExpected = [
            '594115a9-1f01-43f2-918d-da527e302c81' => $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            'f787ac40-e688-4a23-a3de-278002b2ffa6' => $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];
        $usersGroupsToRemove = [
            '10545a18-14ef-4218-baa7-8ad9f7b9a921' => $usersGroups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
            '4ec0985d-4a9d-493d-9fb1-61bab6c4d730' => $usersGroups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            '674cc177-935b-4494-9480-b66a3d9458a4' => $usersGroups['674cc177-935b-4494-9480-b66a3d9458a4'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersNumberOrFail')
            ->with($groupsId)
            ->willReturn($this->groupsUsersNumPaginator);

        $this->groupsUsersNumPaginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupsUsersNumber));

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groupsToRemoveExpected);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsFirstUserByRolOrFail')
            ->with($groupsIdToChangeAdmin, GROUP_ROLES::USER)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroupsToRemove);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($groups, $usersGroups);
        $this->assertReturnIdOk($groupsToRemoveExpected, array_values($usersGroupsToRemove), [], $return);
    }
}

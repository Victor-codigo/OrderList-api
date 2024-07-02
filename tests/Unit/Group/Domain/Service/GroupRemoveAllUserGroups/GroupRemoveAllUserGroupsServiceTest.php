<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupRemoveAllUserGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsOutputDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\GroupRemoveAllUserGroupsService;
use Group\Domain\Service\GroupRemoveAllUserGroups\GroupRemoveUserFromGroupsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveAllUserGroupsServiceTest extends TestCase
{
    private const string USER_ID = 'b23f12cf-75cb-402e-b771-77fba3b0875a';

    private GroupRemoveAllUserGroupsService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|GroupRemoveUserFromGroupsService $groupRemoveUserFromGroupsService;
    private MockObject|PaginatorInterface $userGroupsPaginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->groupRemoveUserFromGroupsService = $this->createMock(GroupRemoveUserFromGroupsService::class);
        $this->userGroupsPaginator = $this->createMock(PaginatorInterface::class);

        $this->object = new GroupRemoveAllUserGroupsService(
            $this->groupRepository,
            $this->userGroupRepository,
            $this->groupRemoveUserFromGroupsService
        );
    }

    /**
     * @return UserGroup[]
     */
    private function getUserGroupsToRemove(): array
    {
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
                [GROUP_ROLES::USER],
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
                [GROUP_ROLES::USER],
                $group
            ),
        ];
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
            fn (UserGroup $userGroup) => $userGroup->getGroupId(),
            $usersGroups
        );
    }

    /** @test */
    public function itShouldRemoveUserGroups(): void
    {
        $pageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $usersGroups = $this->getUserGroupsToRemove();
        $groups = $this->getGroupsOfUsersGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $input = new GroupRemoveAllUserGroupsDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $groupsRemovedExpected = [
            $groups['594115a9-1f01-43f2-918d-da527e302c81'],
            $groups['10545a18-14ef-4218-baa7-8ad9f7b9a921'],
        ];
        $userGroupsRemovedExpected = [
            $groups['4ec0985d-4a9d-493d-9fb1-61bab6c4d730'],
            $groups['674cc177-935b-4494-9480-b66a3d9458a4'],
        ];
        $usersGroupsSetAsAdminExpected = [
            $groups['f787ac40-e688-4a23-a3de-278002b2ffa6'],
        ];

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($input->userId, null, null)
            ->willReturn($this->userGroupsPaginator);

        $this->userGroupsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with($pageItems->getValue())
            ->willReturnCallback(fn () => yield new \ArrayIterator($usersGroups));

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->groupRemoveUserFromGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($groups, $usersGroups)
            ->willReturn(new GroupRemoveAllUserGroupsOutputDto(
                $groupsRemovedExpected,
                $userGroupsRemovedExpected,
                $usersGroupsSetAsAdminExpected,
                $groups
            ));

        $return = $this->object->__invoke($input);

        /** @var GroupRemoveAllUserGroupsOutputDto $groupRemoveAllUserGroupsOutputDto */
        foreach ($return as $groupRemoveAllUserGroupsOutputDto) {
            $this->assertInstanceOf(GroupRemoveAllUserGroupsOutputDto::class, $groupRemoveAllUserGroupsOutputDto);
            $this->assertEquals($groupsRemovedExpected, $groupRemoveAllUserGroupsOutputDto->groupsIdRemoved);
            $this->assertEquals($userGroupsRemovedExpected, $groupRemoveAllUserGroupsOutputDto->usersIdGroupsRemoved);
            $this->assertEquals($usersGroupsSetAsAdminExpected, $groupRemoveAllUserGroupsOutputDto->usersGroupsIdSetAsAdmin);
        }
    }

    /** @test */
    public function itShouldNotRemoveGroupsAndUsersNoGroupsFound(): void
    {
        $pageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $input = new GroupRemoveAllUserGroupsDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($input->userId, null, null)
            ->willReturn($this->userGroupsPaginator);

        $this->userGroupsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with($pageItems->getValue())
            ->willReturnCallback(fn () => yield new \ArrayIterator([]));

        $this->groupRepository
            ->expects($this->never())
            ->method('findGroupsByIdOrFail');

        $this->groupRemoveUserFromGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->with([], [])
            ->willReturn(new GroupRemoveAllUserGroupsOutputDto([], [], [], []));

        $return = $this->object->__invoke($input);

        /** @var GroupRemoveAllUserGroupsOutputDto $groupRemoveAllUserGroupsOutputDto */
        foreach ($return as $groupRemoveAllUserGroupsOutputDto) {
            $this->assertInstanceOf(GroupRemoveAllUserGroupsOutputDto::class, $groupRemoveAllUserGroupsOutputDto);
            $this->assertEmpty($groupRemoveAllUserGroupsOutputDto->groupsIdRemoved);
            $this->assertEmpty($groupRemoveAllUserGroupsOutputDto->usersIdGroupsRemoved);
            $this->assertEmpty($groupRemoveAllUserGroupsOutputDto->usersGroupsIdSetAsAdmin);
        }
    }

    /** @test */
    public function itShouldFailGetAllGroupsUserToRemoveNotFoundException(): void
    {
        $pageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $input = new GroupRemoveAllUserGroupsDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($input->userId, null, null)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupsPaginator
            ->expects($this->never())
            ->method('getAllPages')
            ->with($pageItems->getValue());

        $this->groupRepository
            ->expects($this->never())
            ->method('findGroupsByIdOrFail');

        $this->groupRemoveUserFromGroupsService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DBNotFoundException::class);
        $return = $this->object->__invoke($input);

        foreach ($return as $groupRemoveAllUserGroupsOutputDto) {
        }
    }

    /** @test */
    public function itShouldFailGroupsNotFoundException(): void
    {
        $pageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $usersGroups = $this->getUserGroupsToRemove();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $input = new GroupRemoveAllUserGroupsDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($input->userId, null, null)
            ->willReturn($this->userGroupsPaginator);

        $this->userGroupsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with($pageItems->getValue())
            ->willReturnCallback(fn () => yield new \ArrayIterator($usersGroups));

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->groupRemoveUserFromGroupsService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DBNotFoundException::class);
        $return = $this->object->__invoke($input);

        foreach ($return as $groupRemoveAllUserGroupsOutputDto) {
        }
    }

    /** @test */
    public function itShouldFailUserGroupsRemoveNotFoundException(): void
    {
        $pageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $usersGroups = $this->getUserGroupsToRemove();
        $groups = $this->getGroupsOfUsersGroups();
        $groupsId = $this->getGroupsIdFromUsersGroups($usersGroups);
        $input = new GroupRemoveAllUserGroupsDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($input->userId, null, null)
            ->willReturn($this->userGroupsPaginator);

        $this->userGroupsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with($pageItems->getValue())
            ->willReturnCallback(fn () => yield new \ArrayIterator($usersGroups));

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->groupRemoveUserFromGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $return = $this->object->__invoke($input);

        foreach ($return as $groupRemoveAllUserGroupsOutputDto) {
        }
    }
}

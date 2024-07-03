<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupUserAdd;

use Override;
use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersAlreadyInTheGroupException;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersMaxNumberExceededException;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersPermissionsException;
use Group\Domain\Service\GroupUserAdd\GroupUserAddService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupUserAddServiceTest extends TestCase
{
    private const string GROUP_ID = '76033a53-371e-46df-ac6f-19e67b3263ad';
    private const string GROUP_TYPE_USER_ID = 'a5002966-dbf7-4f76-a862-23a04b5ca465';

    private GroupUserAddService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|GroupRepositoryInterface $groupRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupUserAddService($this->userGroupRepository, $this->groupRepository);
    }

    /**
     * @param string[] $usersId
     */
    private function createGroupUserAddDto(array $usersId): GroupUserAddDto
    {
        $usersIdValueObject = array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $usersId
        );

        return new GroupUserAddDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $usersIdValueObject,
            ValueObjectFactory::createRol(GROUP_ROLES::ADMIN)
        );
    }

    /**
     * @return UserGroup[]
     */
    private function getFindGroupUsersOrFailReturn(): MockObject|PaginatorInterface
    {
        $group = $this->getFindGroupsByIdOrFailReturn()[0];
        $usersGroup = [
            UserGroup::fromPrimitives(self::GROUP_ID, 'c22ba0e4-1af4-4c16-98ed-afd9cdb1a3fb', [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'f8bd3e42-db00-4bba-a0a0-7dc72c281744', [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, '4586fd14-f2de-4c22-b96d-65a8f70ed2ed', [GROUP_ROLES::USER], $group),
        ];

        /** @var MockObject|PaginatorInterface */
        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturnCallback(function () use ($usersGroup) {
                foreach ($usersGroup as $userGroup) {
                    yield $userGroup;
                }
            });

        return $paginator;
    }

    private function getFindGroupsByIdOrFailReturn(): array
    {
        return [Group::fromPrimitives(self::GROUP_ID, 'GroupName', GROUP_TYPE::GROUP, 'Description', null)];
    }

    /**
     * @param string[] $userIds
     *
     * @return UserGroup[]
     */
    private function createUserGroupForIds(string $groupId, array $userIds, GROUP_ROLES $rol, Group $group): array
    {
        return array_map(
            fn (string $userId) => UserGroup::fromPrimitives($groupId, $userId, [$rol], $group),
            $userIds
        );
    }

    private function assertUserGroupIsEqualToUserGroup(array $expectUsersGroup, array $actualUserGroup): bool
    {
        $this->assertEquals(
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getGroupId(),
                $expectUsersGroup
            ),
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getGroupId(),
                $actualUserGroup
            )
        );

        $this->assertEquals(
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getUserId(),
                $expectUsersGroup
            ),
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getUserId(),
                $actualUserGroup
            )
        );

        $this->assertEquals(
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getGroup()->getId(),
                $expectUsersGroup
            ),
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getGroup()->getId(),
                $actualUserGroup
            )
        );

        $this->assertEquals(
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getRoles(),
                $expectUsersGroup
            ),
            array_map(
                fn (UserGroup $userGroup) => $userGroup->getRoles(),
                $actualUserGroup
            )
        );

        return true;
    }

    private function mockMethodsInvoke(GroupUserAddDto $groupUserAddDto, array $expectUsersGroup, ?Exception $saveException = null): void
    {
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupUserAddDto->groupId)
            ->willReturn($this->getFindGroupUsersOrFailReturn());

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupUserAddDto->groupId])
            ->willReturn($this->getFindGroupsByIdOrFailReturn());

        $saveMethod = $this->userGroupRepository
            ->expects($this->any())
            ->method('save')
            ->with($this->callback(fn (array $usersGroupSaved) => $this->assertUserGroupIsEqualToUserGroup($expectUsersGroup, $usersGroupSaved)));

        if (null !== $saveException) {
            $saveMethod->willThrowException($saveException);
        }
    }

    /** @test */
    public function itShouldAddTheUsersToTheGroup(): void
    {
        $usersId = [
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            'a004eb47-6d12-4467-a0d1-2d9fab757f19',
        ];
        $expectUsersGroup = $this->createUserGroupForIds(self::GROUP_ID, $usersId, GROUP_ROLES::ADMIN, $this->getFindGroupsByIdOrFailReturn()[0]);
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->mockMethodsInvoke($groupUserAddDto, $expectUsersGroup);

        $return = $this->object->__invoke($groupUserAddDto);

        $this->assertCount(count($expectUsersGroup), $return);
        $this->assertUserGroupIsEqualToUserGroup($expectUsersGroup, $return);
    }

    /** @test */
    public function itShouldAddOnlyOneUserToTheGroup(): void
    {
        $usersId = [
            'c22ba0e4-1af4-4c16-98ed-afd9cdb1a3fb',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7', // this is not in the group
            'f8bd3e42-db00-4bba-a0a0-7dc72c281744',
        ];
        $expectUsersGroup = $this->createUserGroupForIds(self::GROUP_ID, [$usersId[1]], GROUP_ROLES::ADMIN, $this->getFindGroupsByIdOrFailReturn()[0]);
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->mockMethodsInvoke($groupUserAddDto, $expectUsersGroup);

        $return = $this->object->__invoke($groupUserAddDto);

        $this->assertCount(count($expectUsersGroup), $return);
        $this->assertUserGroupIsEqualToUserGroup($expectUsersGroup, $return);
    }

    /** @test */
    public function itShouldNotAddUsersToTheGroupAllUsersAreAlreadyAdded(): void
    {
        $usersId = [
            'c22ba0e4-1af4-4c16-98ed-afd9cdb1a3fb',
            '4586fd14-f2de-4c22-b96d-65a8f70ed2ed',
            'f8bd3e42-db00-4bba-a0a0-7dc72c281744',
        ];
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->mockMethodsInvoke($groupUserAddDto, []);

        $this->expectException(GroupAddUsersAlreadyInTheGroupException::class);
        $this->object->__invoke($groupUserAddDto);
    }

    /** @test */
    public function itShouldFailGroupDoesNotExists(): void
    {
        $usersId = [
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            'a004eb47-6d12-4467-a0d1-2d9fab757f19',
        ];
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->expectException(DBNotFoundException::class);
        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupUserAddDto->groupId])
            ->willThrowException(DBNotFoundException::fromMessage(''));

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupUsersOrFail');

        $this->object->__invoke($groupUserAddDto);
    }

    private function getGroup(): Group
    {
        return Group::fromPrimitives(self::GROUP_TYPE_USER_ID, 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user', 'image_of_group_type_user');
    }

    /** @test */
    public function itShouldFailGroupTypeUsers(): void
    {
        $usersId = [
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            'a004eb47-6d12-4467-a0d1-2d9fab757f19',
        ];
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);
        $group = $this->getGroup();

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupUserAddDto->groupId])
            ->willReturn([$group]);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupUsersOrFail');

        $this->expectException(GroupAddUsersPermissionsException::class);
        $this->object->__invoke($groupUserAddDto);
    }

    /** @test */
    public function itShouldFailDatabaseErrorConnection(): void
    {
        $usersId = [
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            'a004eb47-6d12-4467-a0d1-2d9fab757f19',
        ];
        $expectUsersGroup = $this->createUserGroupForIds(self::GROUP_ID, $usersId, GROUP_ROLES::ADMIN, $this->getFindGroupsByIdOrFailReturn()[0]);
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->expectException(DBConnectionException::class);
        $this->mockMethodsInvoke($groupUserAddDto, $expectUsersGroup, DBConnectionException::fromMessage(''));

        $this->object->__invoke($groupUserAddDto);
    }

    /** @test */
    public function itShouldFailGroupHasReachItMaximumNumberOfUsers100(): void
    {
        $usersId = [
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            'a004eb47-6d12-4467-a0d1-2d9fab757f19',
        ];
        $groupUserAddDto = $this->createGroupUserAddDto($usersId);

        $this->expectException(GroupAddUsersMaxNumberExceededException::class);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersNumberOrFail')
            ->with($groupUserAddDto->groupId)
            ->willReturn(100 - count($usersId) + 1);

        $this->groupRepository
            ->expects($this->never())
            ->method('findGroupsByIdOrFail');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupUsersOrFail');

        $this->object->__invoke($groupUserAddDto);
    }
}

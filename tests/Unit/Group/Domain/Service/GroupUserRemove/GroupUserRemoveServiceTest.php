<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupUserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRemove\Dto\GroupUserRemoveDto;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveEmptyException;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdmin;
use Group\Domain\Service\GroupUserRemove\GroupUserRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupUserRemoveServiceTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private GroupUserRemoveService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->object = new GroupUserRemoveService($this->userGroupRepository);
    }

    /**
     * @return UserGroup[]
     */
    private function getUsersGroup(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(self::GROUP_ID, 'f425bf79-5a19-31d4-ab56-ed4ca30a7b1a', [], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, '0b17ca3e-490b-3ddb-aa78-35b4ce668dc0', [], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'f1eb9ed5-ccb1-33f4-bb05-19b8b0bea672', [], $group),
        ];
    }

    /**
     * @return UserGroup[]
     */
    private function getUsersGroupAdmin(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(self::GROUP_ID, '2606508b-4516-45d6-93a6-c7cb416b7f3f', [], $group),
        ];
    }

    /** @test */
    public function itShouldRemoveUsersFromTheGroup(): void
    {
        $usersGroup = $this->getUsersGroup();
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
        $input = new GroupUserRemoveDto(ValueObjectFactory::createIdentifier(self::GROUP_ID), $usersId);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($input->groupId, $input->usersId)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersNumberOrFail')
            ->with($input->groupId)
            ->willReturn(count($usersGroup) + 1);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($input->groupId, GROUP_ROLES::ADMIN)
            ->willReturn($this->getUsersGroupAdmin());

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with($usersGroup);

        $return = $this->object->__invoke($input);

        $this->assertEquals($usersId, $return);
    }

    /** @test */
    public function itShouldFailUsersAreNotInTheGroup(): void
    {
        $usersGroup = $this->getUsersGroup();
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
        $input = new GroupUserRemoveDto(ValueObjectFactory::createIdentifier(self::GROUP_ID), $usersId);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($input->groupId, $input->usersId)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldRemoveOnlyOneUserFromTheGroupOthersDoNotBelogToTheGroup(): void
    {
        $usersGroup = $this->getUsersGroup();
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
        $input = new GroupUserRemoveDto(ValueObjectFactory::createIdentifier(self::GROUP_ID), $usersId);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($input->groupId, $input->usersId)
            ->willReturn([$usersGroup[0]]);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersNumberOrFail')
            ->with($input->groupId)
            ->willReturn(count($usersGroup) + 1);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($input->groupId, GROUP_ROLES::ADMIN)
            ->willReturn($this->getUsersGroupAdmin());

        $this->userGroupRepository
            ->expects($this->once())
            ->method('removeUsers')
            ->with([$usersGroup[0]]);

        $return = $this->object->__invoke($input);

        $this->assertEquals([$usersId[0]], $return);
    }

    /** @test */
    public function itShouldFailRemovingCanNotRemoveAllUsersOfTheGroup(): void
    {
        $usersGroup = $this->getUsersGroup();
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
        $input = new GroupUserRemoveDto(ValueObjectFactory::createIdentifier(self::GROUP_ID), $usersId);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($input->groupId, $input->usersId)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersNumberOrFail')
            ->with($input->groupId)
            ->willReturn(count($usersGroup));

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupUsersByRol');

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $this->expectException(GroupUserRemoveEmptyException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingCanNotRemoveAllAdminsFromTheGroup(): void
    {
        $usersGroup = $this->getUsersGroup();
        $usersGroup = array_merge($usersGroup, $this->getUsersGroupAdmin());
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
        $input = new GroupUserRemoveDto(ValueObjectFactory::createIdentifier(self::GROUP_ID), $usersId);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($input->groupId, $input->usersId)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersNumberOrFail')
            ->with($input->groupId)
            ->willReturn(count($usersGroup) + 1);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($input->groupId, GROUP_ROLES::ADMIN)
            ->willReturn($this->getUsersGroupAdmin());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('removeUsers');

        $this->expectException(GroupUserRemoveGroupWithoutAdmin::class);
        $this->object->__invoke($input);
    }
}

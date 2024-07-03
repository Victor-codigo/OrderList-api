<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupUserRoleChange;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRoleChange\Dto\GroupUserRoleChangeDto;
use Group\Domain\Service\GroupUserRoleChange\Exception\GroupWithoutAdminsException;
use Group\Domain\Service\GroupUserRoleChange\GroupUserRoleChangeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupUserRoleChangeTest extends TestCase
{
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const array USERS_ID = [
        'f425bf79-5a19-31d4-ab56-ed4ca30a7b1a',
        '0b13e52d-b058-32fb-8507-10dec634a07c',
        '896c6153-794e-3e94-b62d-95997c8b60ad',
    ];

    private GroupUserRoleChangeService $object;
    private Group $group;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->object = new GroupUserRoleChangeService($this->userGroupRepository);
        $this->group = Group::fromPrimitives(self::GROUP_ID, 'GroupName', GROUP_TYPE::GROUP, 'description', 'image.png');
    }

    /**
     * @param string[] $usersToChangeRole
     */
    private function createGroupUserRoleChangeDto(array $usersToChangeRole, GROUP_ROLES $rol): GroupUserRoleChangeDto
    {
        $usersId = array_map(
            fn (string $userId): Identifier => ValueObjectFactory::createIdentifier($userId),
            $usersToChangeRole
        );

        return new GroupUserRoleChangeDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $usersId,
            ValueObjectFactory::createRol($rol)
        );
    }

    /**
     * @param string[] $usersIdRoleChanged
     */
    private function getFindGroupUsersOrFailReturn(array $usersIdRoleChanged, GROUP_ROLES $groupRol, ?\Exception $exception = null): MockObject|PaginatorInterface
    {
        $usersGroup = array_map(
            fn (string $userId): UserGroup => UserGroup::fromPrimitives(self::GROUP_ID, $userId, [$groupRol], $this->group),
            $usersIdRoleChanged
        );

        /** @var MockObject|PaginatorInterface $paginator */
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

    /**
     * @param UserGroup[] $usersGroup
     * @param UserGroup[] $expectUsersToSave
     */
    private function assertSavedUserGroupAreValid(array $usersGroup, array $expectUsersToSave, GROUP_ROLES $groupRolSaved): bool
    {
        $this->assertEquals($expectUsersToSave, $usersGroup);

        foreach ($usersGroup as $userGroup) {
            $this->assertTrue($userGroup->getRoles()->has(new Rol($groupRolSaved)));
        }

        return true;
    }

    /** @test */
    public function itShouldChangeUsersRoleToGroupAdmin(): void
    {
        $expectUsersToSave = $this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::USER);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willReturn($expectUsersToSave);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $usersGroup): bool => $this->assertSavedUserGroupAreValid($usersGroup, iterator_to_array($expectUsersToSave), GROUP_ROLES::ADMIN)));

        $input = $this->createGroupUserRoleChangeDto(self::USERS_ID, GROUP_ROLES::ADMIN);
        $return = $this->object->__invoke($input);

        $usersId = array_map(
            fn (Identifier $userId): ?string => $userId->getValue(),
            $return
        );

        $this->assertEquals(self::USERS_ID, $usersId);
    }

    /** @test */
    public function itShouldChangeUsersRoleToGroupUser(): void
    {
        $users = $this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::ADMIN);
        $expectUsersToSave = $this->getFindGroupUsersOrFailReturn([self::USERS_ID[1], self::USERS_ID[2]], GROUP_ROLES::USER);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willReturn($users);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $usersGroup): bool => $this->assertSavedUserGroupAreValid($usersGroup, iterator_to_array($expectUsersToSave), GROUP_ROLES::USER)));

        $input = $this->createGroupUserRoleChangeDto([self::USERS_ID[1], self::USERS_ID[2]], GROUP_ROLES::USER);
        $return = $this->object->__invoke($input);

        $usersId = array_map(
            fn (Identifier $userId): ?string => $userId->getValue(),
            $return
        );

        $this->assertEquals([self::USERS_ID[1], self::USERS_ID[2]], $usersId);
    }

    /** @test */
    public function itShouldChangeThreeUsersOneOfThenIsNotFromTheGroup(): void
    {
        $expectUsersToSave = $this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::ADMIN);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willReturn($this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::ADMIN));

        $this->userGroupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $usersGroup): bool => $this->assertSavedUserGroupAreValid($usersGroup, iterator_to_array($expectUsersToSave), GROUP_ROLES::ADMIN)));

        $usersIdToChangeRole = self::USERS_ID;
        $usersIdToChangeRole[] = '99b92adb-12f2-4276-b2c0-1f0c48980d45';
        $input = $this->createGroupUserRoleChangeDto($usersIdToChangeRole, GROUP_ROLES::ADMIN);
        $return = $this->object->__invoke($input);

        $usersId = array_map(
            fn (Identifier $userId): ?string => $userId->getValue(),
            $return
        );

        $this->assertEquals(self::USERS_ID, $usersId);
    }

    /** @test */
    public function itShouldNotChangeUsersRolNoneOfTheUsersBelongsToTheGroup(): void
    {
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willReturn($this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::ADMIN));

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $usersIdToChangeRole = ['99b92adb-12f2-4276-b2c0-1f0c48980d45', '94ab220d-636e-40b9-a8bd-429293f260ef'];
        $input = $this->createGroupUserRoleChangeDto($usersIdToChangeRole, GROUP_ROLES::ADMIN);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNoAdminsInTheGroup(): void
    {
        $this->expectException(GroupWithoutAdminsException::class);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willReturn($this->getFindGroupUsersOrFailReturn(self::USERS_ID, GROUP_ROLES::ADMIN));

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $input = $this->createGroupUserRoleChangeDto(self::USERS_ID, GROUP_ROLES::USER);
        $return = $this->object->__invoke($input);

        $usersId = array_map(
            fn (Identifier $userId): ?string => $userId->getValue(),
            $return
        );

        $this->assertEquals(self::USERS_ID, $usersId);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with(self::GROUP_ID)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('save');

        $input = $this->createGroupUserRoleChangeDto([], GROUP_ROLES::ADMIN);
        $this->object->__invoke($input);
    }
}

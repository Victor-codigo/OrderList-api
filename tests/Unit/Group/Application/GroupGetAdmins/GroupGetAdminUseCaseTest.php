<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetAdmins;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsInputDto;
use Group\Application\GroupGetAdmins\Exception\GroupGetAdminsGroupNotFoundException;
use Group\Application\GroupGetAdmins\Exception\GroupGetAdminsGroupNotPermissionsException;
use Group\Application\GroupGetAdmins\GroupGetAdminsUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupGetAdminUseCaseTest extends TestCase
{
    private GroupGetAdminsUseCase $object;
    private MockObject|ValidatorInterface $validator;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidationInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->userSession = $this->createMock(UserShared::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new GroupGetAdminsUseCase($this->validator, $this->userGroupRepository);
    }

    private function getGroupUsers(string|null $setGroupId = null): \Iterator
    {
        $group = $this->createMock(Group::class);

        $usersGroup = new \ArrayIterator([
            UserGroup::fromPrimitives('group id 1', 'user id 1', [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives('group id 2', 'user id 2', [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives('group id 3', 'user id 3', [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives($setGroupId ?? 'group id 4', 'user id 4', [GROUP_ROLES::ADMIN], $group),
        ]);

        return $usersGroup;
    }

    /** @test */
    public function itShouldFailValidationGroupIdWrong(): void
    {
        $groupId = 'group id';
        $input = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]);

        $this->expectException(ValueObjectValidationException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $groupId = 'group id';
        $input = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(GroupGetAdminsGroupNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailUserSessionDoesNotBelongsToGroup(): void
    {
        $groupId = 'group id';
        $groupUsers = $this->getGroupUsers();
        $input = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn($groupUsers);

        $this->expectException(GroupGetAdminsGroupNotPermissionsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldGetAdminsOfTheGroupUserSessionIsAdmin(): void
    {
        $groupId = 'group id';
        $userId = 'user id 1';
        $groupUsers = $this->getGroupUsers($groupId);
        $groupAdmins = array_filter(iterator_to_array($groupUsers), fn (UserGroup $userGroup) => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN)));
        $groupAdminsIds = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(), $groupAdmins);
        $input = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn($groupUsers);

        $this->userSession
            ->expects($this->any())
            ->method('getId')
            ->willReturn(ValueObjectFactory::createIdentifier($userId));

        $return = $this->object->__invoke($input);

        $this->assertTrue(property_exists($return, 'isAdmin'));
        $this->assertTrue(property_exists($return, 'admins'));
        $this->assertTrue($return->isAdmin);
        $this->assertEquals($return->admins, $groupAdminsIds);
    }

    /** @test */
    public function itShouldGetAdminsOfTheGroupUserSessionIsNotAdmin(): void
    {
        $groupId = 'group id';
        $userId = 'user id 2';
        $groupUsers = $this->getGroupUsers($groupId);
        $groupAdmins = array_filter(iterator_to_array($groupUsers), fn (UserGroup $userGroup) => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN)));
        $groupAdminsIds = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(), $groupAdmins);
        $input = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn($groupUsers);

        $this->userSession
            ->expects($this->any())
            ->method('getId')
            ->willReturn(ValueObjectFactory::createIdentifier($userId));

        $return = $this->object->__invoke($input);

        $this->assertTrue(property_exists($return, 'isAdmin'));
        $this->assertTrue(property_exists($return, 'admins'));
        $this->assertFalse($return->isAdmin);
        $this->assertEquals($return->admins, $groupAdminsIds);
    }
}

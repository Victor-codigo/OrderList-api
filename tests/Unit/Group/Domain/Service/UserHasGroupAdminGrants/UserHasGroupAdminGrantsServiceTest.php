<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\UserHasGroupAdminGrants;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class UserHasGroupAdminGrantsServiceTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private UserHasGroupAdminGrantsService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->object = new UserHasGroupAdminGrantsService($this->userGroupRepository);
    }

    private function getUserSession(): User
    {
        return User::fromPrimitives(self::GROUP_USER_ADMIN_ID, '', '', '', [USER_ROLES::USER]);
    }

    private function getUserSessionNotValid(): User
    {
        return User::fromPrimitives('0e588ccf-0bda-430b-bb82-c89e75f615a0', '', '', '', [USER_ROLES::USER]);
    }

    private function getGroupUsersAdmins(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(self::GROUP_ID, self::GROUP_USER_ADMIN_ID, [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'other admin 1', [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'other admin 2', [GROUP_ROLES::ADMIN], $group),
        ];
    }

    /** @test */
    public function itShouldReturnThatIsAValidAdminOfTheGroupOneAdmin(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $userSession = $this->getUserSession();
        $userGroupAdmin = $this->getGroupUsersAdmins();

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($groupId, GROUP_ROLES::ADMIN)
            ->willReturn([$userGroupAdmin[0]]);

        $return = $this->object->__invoke(
            $userSession,
            $groupId
        );

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldReturnThatIsAValidAdminOfTheGroupManyAdmins(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $userSession = $this->getUserSession();
        $userGroupAdmin = $this->getGroupUsersAdmins();

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($groupId, GROUP_ROLES::ADMIN)
            ->willReturn($userGroupAdmin);

        $return = $this->object->__invoke(
            $userSession,
            $groupId
        );

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldReturnThatIsNotAValidAdminOfTheGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $userSession = $this->getUserSessionNotValid();
        $userGroupAdmin = $this->getGroupUsersAdmins();

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByRol')
            ->with($groupId, GROUP_ROLES::ADMIN)
            ->willReturn($userGroupAdmin);

        $return = $this->object->__invoke(
            $userSession,
            $groupId
        );

        $this->assertFalse($return);
    }
}

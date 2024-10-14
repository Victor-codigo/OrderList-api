<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\UserHasGroupAdminGrants;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserHasGroupAdminGrantsServiceTest extends TestCase
{
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private UserHasGroupAdminGrantsService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->object = new UserHasGroupAdminGrantsService($this->userGroupRepository);
    }

    private function getUserSession(): UserShared
    {
        return UserShared::fromPrimitives(self::GROUP_USER_ADMIN_ID, '', '', [USER_ROLES::USER], null, new \DateTime());
    }

    private function getUserSessionNotValid(): UserShared
    {
        return UserShared::fromPrimitives('0e588ccf-0bda-430b-bb82-c89e75f615a0', '', '', [USER_ROLES::USER], null, new \DateTime());
    }

    /**
     * @return UserGroup[]
     */
    private function getGroupUsersAdmins(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(self::GROUP_ID, self::GROUP_USER_ADMIN_ID, [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'other admin 1', [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, 'other admin 2', [GROUP_ROLES::ADMIN], $group),
        ];
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

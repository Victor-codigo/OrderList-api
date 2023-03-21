<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserFirstLogin;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserCreateGroup\Dto\UserCreateGroupDto;
use User\Domain\Service\UserCreateGroup\Exception\UserCreateGroupUserException;
use User\Domain\Service\UserCreateGroup\UserCreateGroupService;
use User\Domain\Service\UserFirstLogin\Dto\UserFirstLoginDto;
use User\Domain\Service\UserFirstLogin\Exception\UserFirstLoginCreateGroupException;
use User\Domain\Service\UserFirstLogin\UserFirstLoginService;

class UserFirstLoginServiceTest extends TestCase
{
    private UserFirstLoginService $object;
    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|UserCreateGroupService $userCreateGroupService;
    private MockObject|User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userCreateGroupService = $this->createMock(UserCreateGroupService::class);
        $this->user = $this->createMock(User::class);
        $this->object = new UserFirstLoginService($this->userRepository, $this->userCreateGroupService);
    }

    /** @test */
    public function itShouldLeaveItIsNotFirstLogin(): void
    {
        $input = new UserFirstLoginDto($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::USER]));

        $this->userCreateGroupService
            ->expects($this->never())
            ->method('__invoke');

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCouldNotCreateUserGroup(): void
    {
        $groupName = ValueObjectFactory::createName('groupName');
        $userCreateGroupDto = new UserCreateGroupDto($groupName);
        $input = new UserFirstLoginDto($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->user
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->userCreateGroupService
            ->expects($this->once())
            ->method('__invoke')
            ->with($userCreateGroupDto)
            ->willThrowException(new UserCreateGroupUserException());

        $this->expectException(UserFirstLoginCreateGroupException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldCreateTheUserGroupAndChangeRole(): void
    {
        $groupName = ValueObjectFactory::createName('groupName');
        $userCreateGroupDto = new UserCreateGroupDto($groupName);
        $input = new UserFirstLoginDto($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->user
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->user
            ->expects($this->once())
            ->method('setRoles')
            ->with(Roles::create([USER_ROLES::USER]));

        $this->userCreateGroupService
            ->expects($this->once())
            ->method('__invoke')
            ->with($userCreateGroupDto);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->object->__invoke($input);
    }
}

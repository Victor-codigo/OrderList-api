<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserFirstLogin;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private string $appName;
    private string $systemKey;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userCreateGroupService = $this->createMock(UserCreateGroupService::class);
        $this->user = $this->createMock(User::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->appName = 'appName';
        $this->systemKey = 'systemKey';

        $this->object = new UserFirstLoginService(
            $this->userRepository,
            $this->userCreateGroupService,
            $this->moduleCommunication,
            $this->appName,
            $this->systemKey
        );
    }

    #[Test]
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

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailCouldNotCreateUserGroup(): void
    {
        $groupName = ValueObjectFactory::createNameWithSpaces('groupName');
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

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(UserFirstLoginCreateGroupException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldCreateTheUserGroupAndChangeRoleAndCreateNotification(): void
    {
        $userName = ValueObjectFactory::createNameWithSpaces('userName');
        $userId = ValueObjectFactory::createIdentifier('user id');
        $userCreateGroupDto = new UserCreateGroupDto($userName);
        $input = new UserFirstLoginDto($this->user);
        $appName = $this->appName;
        $systemKey = $this->systemKey;

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->user
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($userName);

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

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationData) use ($userId, $userName, $appName, $systemKey): bool {
                $this->assertEquals([$userId], $notificationData->content['users_id']);
                $this->assertEquals('NOTIFICATION_USER_REGISTERED', $notificationData->content['type']);
                $this->assertEquals($systemKey, $notificationData->content['system_key']);
                $this->assertEquals($userName, $notificationData->content['notification_data']['user_name']);
                $this->assertEquals($appName, $notificationData->content['notification_data']['domain_name']);

                return true;
            }))
            ->willReturn(new ResponseDto(['id' => 'notification id']));

        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailNotificationUserRegistered(): void
    {
        $userName = ValueObjectFactory::createNameWithSpaces('userName');
        $userId = ValueObjectFactory::createIdentifier('user id');
        $userCreateGroupDto = new UserCreateGroupDto($userName);
        $input = new UserFirstLoginDto($this->user);
        $appName = $this->appName;
        $systemKey = $this->systemKey;

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->user
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($userName);

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

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationData) use ($userId, $userName, $appName, $systemKey): bool {
                $this->assertEquals([$userId], $notificationData->content['users_id']);
                $this->assertEquals('NOTIFICATION_USER_REGISTERED', $notificationData->content['type']);
                $this->assertEquals($systemKey, $notificationData->content['system_key']);
                $this->assertEquals($userName, $notificationData->content['notification_data']['user_name']);
                $this->assertEquals($appName, $notificationData->content['notification_data']['domain_name']);

                return true;
            }))
            ->willReturn(new ResponseDto([], ['error' => 'data']));

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }
}

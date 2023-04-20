<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserRemove;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveInputDto;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupNotificationException;
use Group\Application\GroupUserRemove\GroupUserRemoveUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupUserRemove\Dto\GroupUserRemoveDto;
use Group\Domain\Service\GroupUserRemove\GroupUserRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupUserRemoveUseCaseTest extends TestCase
{
    private const SYSTEM_KEY = 'systemKeyForDev';

    private GroupUserRemoveUseCase $object;
    private MockObject|ValidationInterface $validator;
    private MockObject|UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService;
    private MockObject|GroupUserRemoveService $groupUserRemoveService;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|User $userSession;
    private MockObject|Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidationInterface::class);
        $this->userHasGroupAdminGrantsService = $this->createMock(UserHasGroupAdminGrantsService::class);
        $this->groupUserRemoveService = $this->createMock(GroupUserRemoveService::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userSession = $this->createMock(User::class);
        $this->group = $this->createMock(Group::class);
        $this->object = new GroupUserRemoveUseCase(
            $this->validator,
            $this->userHasGroupAdminGrantsService,
            $this->groupUserRemoveService,
            $this->moduleCommunication,
            $this->groupRepository,
            self::SYSTEM_KEY
        );
    }

    /** @test */
    public function itShouldRemoveTheUsersFromTheGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createName('group name');
        $usersId = [
            ValueObjectFactory::createIdentifier('user id 1'),
            ValueObjectFactory::createIdentifier('user id 2'),
        ];
        $input = new GroupUserRemoveInputDto($this->userSession, $groupId->getValue(), $usersId);

        $this->validator
            ->expects($this->any())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userHasGroupAdminGrantsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->userSession, $groupId)
            ->willReturn(true);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupId])
            ->willReturn([$this->group]);

        $this->groupUserRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupUserRemoveDto $serviceInput) use ($groupId, $usersId) {
                $this->assertEquals($groupId, $serviceInput->groupId);
                $this->assertEquals($usersId, $serviceInput->usersId);

                return true;
            }))
            ->willReturn($usersId);

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($usersId, $groupName) {
                $this->assertEquals($usersId, $notificationDto->content['users_id']);
                $this->assertEquals($groupName, $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_USER_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $return = $this->object->__invoke($input);

        $this->assertEquals($usersId, $return->usersId);
    }

    /** @test */
    public function itShouldFailRemoveTheUsersFromTheGroupNotificationError(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createName('group name');
        $usersId = [
            ValueObjectFactory::createIdentifier('user id 1'),
            ValueObjectFactory::createIdentifier('user id 2'),
        ];
        $input = new GroupUserRemoveInputDto($this->userSession, $groupId->getValue(), $usersId);

        $this->validator
            ->expects($this->any())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userHasGroupAdminGrantsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->userSession, $groupId)
            ->willReturn(true);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupId])
            ->willReturn([$this->group]);

        $this->groupUserRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupUserRemoveDto $serviceInput) use ($groupId, $usersId) {
                $this->assertEquals($groupId, $serviceInput->groupId);
                $this->assertEquals($usersId, $serviceInput->usersId);

                return true;
            }))
            ->willReturn($usersId);

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($usersId, $groupName) {
                $this->assertEquals($usersId, $notificationDto->content['users_id']);
                $this->assertEquals($groupName, $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_USER_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(GroupUserRemoveGroupNotificationException::class);
        $this->object->__invoke($input);
    }
}

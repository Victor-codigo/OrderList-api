<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemove;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotificationException;
use Group\Application\GroupRemove\GroupRemoveUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupRemoveUseCaseTest extends TestCase
{
    private const SYSTEM_KEY = 'systemKeForDev';

    private GroupRemoveUseCase $object;
    private MockObject|GroupRemoveService $groupRemoveService;
    private MockObject|UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|ValidationInterface $validator;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|User $userSession;
    private MockObject|Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRemoveService = $this->createMock(GroupRemoveService::class);
        $this->userHasGroupAdminGrantsService = $this->createMock(UserHasGroupAdminGrantsService::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->userSession = $this->createMock(User::class);
        $this->group = $this->createMock(Group::class);
        $this->object = new GroupRemoveUseCase(
            $this->groupRemoveService,
            $this->userHasGroupAdminGrantsService,
            $this->groupRepository,
            $this->validator,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /** @test */
    public function itShouldRemoveTheGroup(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createName('group name');
        $input = new GroupRemoveInputDto($this->userSession, $groupId->getValue());

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

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

        $this->groupRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupRemoveDto $serviceInput) use ($groupId) {
                $this->assertEquals($groupId, $serviceInput->groupId);

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupName) {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertEquals($groupName, $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $return = $this->object->__invoke($input);

        $this->assertEquals($groupId, $return->groupRemovedId);
    }

    /** @test */
    public function itShouldFailRemoveTheGroupNotificationError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createName('group name');
        $input = new GroupRemoveInputDto($this->userSession, $groupId->getValue());

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

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

        $this->groupRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupRemoveDto $serviceInput) use ($groupId) {
                $this->assertEquals($groupId, $serviceInput->groupId);

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupName) {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertEquals($groupName, $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(GroupRemoveGroupNotificationException::class);
        $this->object->__invoke($input);
    }
}

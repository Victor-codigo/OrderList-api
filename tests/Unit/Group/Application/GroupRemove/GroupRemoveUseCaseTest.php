<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotFoundException;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotificationException;
use Group\Application\GroupRemove\Exception\GroupRemovePermissionsException;
use Group\Application\GroupRemove\GroupRemoveUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveUseCaseTest extends TestCase
{
    private const string SYSTEM_KEY = 'systemKeForDev';

    private GroupRemoveUseCase $object;
    private MockObject|GroupRemoveService $groupRemoveService;
    private MockObject|UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|ValidationInterface $validator;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRemoveService = $this->createMock(GroupRemoveService::class);
        $this->userHasGroupAdminGrantsService = $this->createMock(UserHasGroupAdminGrantsService::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->userSession = $this->createMock(UserShared::class);
        $this->object = new GroupRemoveUseCase(
            $this->groupRemoveService,
            $this->userHasGroupAdminGrantsService,
            $this->groupRepository,
            $this->validator,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /**
     * @return Group[]
     */
    private function getGroupsData(): array
    {
        return [
            Group::fromPrimitives(
                'ddb1a26e-ed57-4e83-bc5c-0ee6f6f5c8f8',
                'Group 1',
                GROUP_TYPE::USER,
                'This is a group 1',
                null
            ),

            Group::fromPrimitives(
                'c7aaafee-8ccf-41fd-b212-ccfb1a9be832',
                'Group 2',
                GROUP_TYPE::USER,
                'This is a group 2',
                null
            ),
        ];
    }

    /** @test */
    public function itShouldRemoveTheGroups(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupsId = [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
        ];
        $groupsIdPlain = array_map(fn (Identifier $groupId) => $groupId->getValue(), $groupsId);
        $groups = $this->getGroupsData();
        $groupsNames = array_map(fn (Group $groupName) => $groupName->getName(), $groups);
        $input = new GroupRemoveInputDto($this->userSession, $groupsIdPlain);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userHasGroupAdminGrantsService
            ->expects($this->exactly(count($groupsId)))
            ->method('__invoke')
            ->with(
                $this->userSession,
                $this->callback(function (Identifier $groupId) use ($groupsId) {
                    $this->assertContainsEquals($groupId, $groupsId);

                    return true;
                }))
            ->willReturn(true);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->groupRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupRemoveDto $serviceInput) use ($groupsId) {
                $this->assertEquals($groupsId, $serviceInput->groupsId);

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->exactly(count($groups)))
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupsNames) {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertContainsEquals($notificationDto->content['notification_data']['group_name'], $groupsNames);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $return = $this->object->__invoke($input);

        $this->assertEquals($groupsId, $return->groupsRemovedId);
    }

    /** @test */
    public function itShouldFailRemoveTheGroupNotificationError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupsId = [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
        ];
        $groupsIdPlain = array_map(fn (Identifier $groupId) => $groupId->getValue(), $groupsId);
        $groups = $this->getGroupsData();
        $groupsNames = array_map(fn (Group $groupName) => $groupName->getName(), $groups);
        $input = new GroupRemoveInputDto($this->userSession, $groupsIdPlain);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userHasGroupAdminGrantsService
            ->expects($this->exactly(count($groupsId)))
            ->method('__invoke')
            ->with(
                $this->userSession,
                $this->callback(function (Identifier $groupId) use ($groupsId) {
                    $this->assertContainsEquals($groupId, $groupsId);

                    return true;
                }))
            ->willReturn(true);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->groupRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupRemoveDto $serviceInput) use ($groupsId) {
                $this->assertEquals($groupsId, $serviceInput->groupsId);

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->exactly(count($groups)))
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupsNames) {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertContainsEquals($notificationDto->content['notification_data']['group_name'], $groupsNames);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_REMOVED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(GroupRemoveGroupNotificationException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemoveUserIsNotAdminOfTheGroup(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
        ];
        $groupsIdPlain = array_map(fn (Identifier $groupId) => $groupId->getValue(), $groupsId);
        $input = new GroupRemoveInputDto($this->userSession, $groupsIdPlain);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userHasGroupAdminGrantsService
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->userSession,
                $this->callback(function (Identifier $groupId) use ($groupsId) {
                    $this->assertContainsEquals($groupId, $groupsId);

                    return true;
                }))
            ->willReturn(false);

        $this->groupRepository
            ->expects($this->never())
            ->method('findGroupsByIdOrFail');

        $this->groupRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupRemovePermissionsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemoveGroupIdNotFound(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
        ];
        $groupsIdPlain = array_map(fn (Identifier $groupId) => $groupId->getValue(), $groupsId);
        $input = new GroupRemoveInputDto($this->userSession, $groupsIdPlain);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userHasGroupAdminGrantsService
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->userSession,
                $this->callback(function (Identifier $groupId) use ($groupsId) {
                    $this->assertContainsEquals($groupId, $groupsId);

                    return true;
                }))
            ->willThrowException(new DBNotFoundException());

        $this->groupRepository
            ->expects($this->never())
            ->method('findGroupsByIdOrFail');

        $this->groupRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupRemoveGroupNotFoundException::class);
        $this->object->__invoke($input);
    }
}

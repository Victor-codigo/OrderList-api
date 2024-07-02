<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemoveAllUserGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllGroupsOutputDto;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsInputDto;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserGroupsNotFoundException;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserGroupsNotificationException;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserSystemKeyException;
use Group\Application\GroupRemoveAllUserGroups\GroupRemoveAllUserGroupsUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsOutputDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\GroupRemoveAllUserGroupsService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveAllUserGroupsUseCaseTest extends TestCase
{
    private const string CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    private const string SYSTEM_KEY = 'systemKey';

    private GroupRemoveAllUserGroupsUseCase $object;
    private MockObject|GroupRemoveAllUserGroupsService $groupRemoveAllUserGroupsService;
    private MockObject|UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|UserShared $userSession;
    private MockObject|ValidationInterface $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRemoveAllUserGroupsService = $this->createMock(GroupRemoveAllUserGroupsService::class);
        $this->userHasGroupAdminGrantsService = $this->createMock(UserHasGroupAdminGrantsService::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->userSession = $this->createMock(UserShared::class);
        $this->validation = $this->createMock(ValidationInterface::class);
        $this->object = new GroupRemoveAllUserGroupsUseCase(
            $this->groupRemoveAllUserGroupsService,
            $this->userHasGroupAdminGrantsService,
            $this->moduleCommunication,
            $this->validation,
            self::SYSTEM_KEY
        );
    }

    private function getGroupRemoveAllUserGroupsDto(Identifier $userId): GroupRemoveAllUserGroupsDto
    {
        return new GroupRemoveAllUserGroupsDto($userId);
    }

    private function getGroupRemoveAllUserGroupsOutputDto(): GroupRemoveAllUserGroupsOutputDto
    {
        $group = $this->createMock(Group::class);
        $group
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
                ValueObjectFactory::createIdentifier('group 1 id'),
                ValueObjectFactory::createIdentifier('group 2 id'),
            );

        $group
            ->expects($this->any())
            ->method('getName')
            ->willReturnOnConsecutiveCalls(
                ValueObjectFactory::createNameWithSpaces('Group 1 name'),
                ValueObjectFactory::createNameWithSpaces('Group 2 name'),
            );

        return new GroupRemoveAllUserGroupsOutputDto([
            Group::fromPrimitives(
                'group 1 id',
                'Group 1 name',
                GROUP_TYPE::USER,
                'Group 1 description',
                null
            ),
            Group::fromPrimitives(
                'group 2 id',
                'Group 2 name',
                GROUP_TYPE::USER,
                'Group 2 description',
                null
            ),
        ], [
            UserGroup::fromPrimitives(
                'group 1 id',
                'User removed 1 id',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group 2 id',
                'User removed 2 id',
                [GROUP_ROLES::USER],
                $group
            ),
        ], [
            UserGroup::fromPrimitives(
                'group 1 id',
                'User set admin 1 id',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group 2 id',
                'User set admin 2 id',
                [GROUP_ROLES::USER],
                $group
            ),
        ], [
            $group,
            $group,
        ]
        );
    }

    /**
     * @param Identifier[]     $usersId
     * @param NameWithSpaces[] $groupsNames
     *
     * @return ModuleCommunicationConfigDto[]
     */
    private function getModuleCommunicationConfigDto(array $usersId, array $groupsNames, string $systemKey, ?JwtToken $tokenSession): array
    {
        $notificationSetAsAdminConfig = [];
        foreach ($usersId as $key => $userId) {
            $content = [
                'users_id' => [$userId->getValue()],
                'type' => NOTIFICATION_TYPE::GROUP_USER_SET_AS_ADMIN->value,
                'notification_data' => [
                    'group_name' => $groupsNames[$key]->getValue(),
                ],
                'system_key' => $systemKey,
            ];

            $notificationSetAsAdminConfig[] = new ModuleCommunicationConfigDto(
                'notification_create',
                'POST',
                true, [
                    'api_version' => 1,
                ],
                [],
                [],
                self::CONTENT_TYPE_APPLICATION_JSON,
                $content,
                [],
                []
            );
        }

        return $notificationSetAsAdminConfig;
    }

    private function getModuleCommunicationResponseDto(RESPONSE_STATUS $status): ResponseDto
    {
        return new ResponseDto([], [], '', $status);
    }

    private function getUsersSetAsAdminNotificationData(): array
    {
        return [
            'usersId' => [
                ValueObjectFactory::createIdentifier('User set admin 1 id'),
                ValueObjectFactory::createIdentifier('User set admin 2 id'),
            ],
            'groupNames' => [
                ValueObjectFactory::createNameWithSpaces('Group 1 name'),
                ValueObjectFactory::createNameWithSpaces('Group 2 name'),
            ],
        ];
    }

    private function getGroupRemoveAllGroupsOutputDto(GroupRemoveAllUserGroupsOutputDto $groupRemoveAllUserGroupsService): GroupRemoveAllGroupsOutputDto
    {
        $groupsIdRemoved = array_map(
            fn (Group $group) => $group->getId(),
            $groupRemoveAllUserGroupsService->groupsIdRemoved
        );
        $groupsIdUserRemoved = array_map(
            fn (UserGroup $group) => $group->getGroupId(),
            $groupRemoveAllUserGroupsService->usersIdGroupsRemoved
        );
        $groupsIdUserSetAsAdmin = array_map(
            fn (UserGroup $group) => [
                'group_id' => $group->getGroupId(),
                'user_id' => $group->getUserId(),
            ],
            $groupRemoveAllUserGroupsService->usersGroupsIdSetAsAdmin
        );

        return new GroupRemoveAllGroupsOutputDto(
            $groupsIdRemoved,
            $groupsIdUserRemoved,
            $groupsIdUserSetAsAdmin
        );
    }

    /** @test */
    public function itShouldRemoveGroupsAndSendNotifications(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupRemoveAllUserGroupsService = $this->getGroupRemoveAllUserGroupsOutputDto();
        $getGroupRemoveAllUserGroupsDto = $this->getGroupRemoveAllUserGroupsDto($userId);
        $usersSetAsAdminNotificationData = $this->getUsersSetAsAdminNotificationData();
        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto(
            $usersSetAsAdminNotificationData['usersId'],
            $usersSetAsAdminNotificationData['groupNames'],
            self::SYSTEM_KEY,
            null,
        );
        $moduleCommunicationResponseDto = $this->getModuleCommunicationResponseDto(RESPONSE_STATUS::OK);
        $input = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            self::SYSTEM_KEY
        );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->groupRemoveAllUserGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($getGroupRemoveAllUserGroupsDto)
            ->willReturnCallback(fn () => yield $groupRemoveAllUserGroupsService);

        $this->moduleCommunication
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationSetAsAdminConfig) use ($moduleCommunicationConfigDto) {
                $this->assertContainsEquals($notificationSetAsAdminConfig, $moduleCommunicationConfigDto);

                return true;
            }))
            ->willReturn($moduleCommunicationResponseDto);

        $return = $this->object->__invoke($input);
        $expected = $this->getGroupRemoveAllGroupsOutputDto($groupRemoveAllUserGroupsService);

        $this->assertEquals($expected, $return);
    }

    /** @test */
    public function itShouldFailNoGroupsFoundException(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $getGroupRemoveAllUserGroupsDto = $this->getGroupRemoveAllUserGroupsDto($userId);
        $input = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            self::SYSTEM_KEY
        );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->groupRemoveAllUserGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($getGroupRemoveAllUserGroupsDto)
            ->willThrowException(new DBNotFoundException());

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupRemoveAllUserGroupsNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModuleNotificationError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupRemoveAllUserGroupsService = $this->getGroupRemoveAllUserGroupsOutputDto();
        $getGroupRemoveAllUserGroupsDto = $this->getGroupRemoveAllUserGroupsDto($userId);
        $usersSetAsAdminNotificationData = $this->getUsersSetAsAdminNotificationData();
        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto(
            $usersSetAsAdminNotificationData['usersId'],
            $usersSetAsAdminNotificationData['groupNames'],
            self::SYSTEM_KEY,
            null,
        );
        $moduleCommunicationResponseDto = $this->getModuleCommunicationResponseDto(RESPONSE_STATUS::ERROR);
        $input = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            self::SYSTEM_KEY
        );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->groupRemoveAllUserGroupsService
            ->expects($this->once())
            ->method('__invoke')
            ->with($getGroupRemoveAllUserGroupsDto)
            ->willReturnCallback(fn () => yield $groupRemoveAllUserGroupsService);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationSetAsAdminConfig) use ($moduleCommunicationConfigDto) {
                $this->assertContainsEquals($notificationSetAsAdminConfig, $moduleCommunicationConfigDto);

                return true;
            }))
            ->willReturn($moduleCommunicationResponseDto);

        $this->expectException(GroupRemoveAllUserGroupsNotificationException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailSystemKeyIsNull(): void
    {
        $input = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            null
        );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->groupRemoveAllUserGroupsService
            ->expects($this->never())
            ->method('__invoke');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupRemoveAllUserSystemKeyException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailSystemKeyIsWrong(): void
    {
        $input = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            'wrong system key'
        );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->groupRemoveAllUserGroupsService
            ->expects($this->never())
            ->method('__invoke');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupRemoveAllUserSystemKeyException::class);
        $this->object->__invoke($input);
    }
}

<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupCreate;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use Group\Application\GroupCreate\Exception\GroupCreateNotificationException;
use Group\Application\GroupCreate\GroupCreateUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;
use Group\Domain\Service\GroupCreate\GroupCreateService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupCreateUseCaseTest extends TestCase
{
    private const string SYSTEM_KEY = 'systemKeyForDev';

    private GroupCreateUseCase $object;
    private MockObject&GroupCreateService $groupCreateService;
    private MockObject&ValidationInterface $validator;
    private MockObject&ModuleCommunicationInterface $moduleCommunication;
    private MockObject&Group $group;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupCreateService = $this->createMock(GroupCreateService::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->group = $this->createMock(Group::class);
        $this->object = new GroupCreateUseCase(
            $this->groupCreateService,
            $this->validator,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    #[Test]
    public function itShouldCreateTheGroupWithCreateNotification(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createNameWithSpaces('group name');
        $description = ValueObjectFactory::createDescription('description');
        $groupType = ValueObjectFactory::createGroupType(GROUP_TYPE::GROUP);
        $image = ValueObjectFactory::createGroupImage($this->createMock(UploadedFileInterface::class));
        $input = new GroupCreateInputDto($userId, $groupName->getValue(), $description->getValue(), $groupType->getValue()->value, $image->getValue(), true);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->groupCreateService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupCreateDto $input) use ($userId, $groupName, $description, $groupType, $image): bool {
                $this->assertEquals($userId, $input->userCreatorId);
                $this->assertEquals($groupName, $input->name);
                $this->assertEquals($description, $input->description);
                $this->assertEquals($groupType, $input->type);
                $this->assertEquals($image, $input->image);

                return true;
            }))
            ->willReturn($this->group);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupName): bool {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertEquals($groupName->getValue(), $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_CREATED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->group
            ->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($groupId, $return);
    }

    #[Test]
    public function itShouldCreateTheGroupWithoutCreateNotification(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $groupName = ValueObjectFactory::createNameWithSpaces('group name');
        $description = ValueObjectFactory::createDescription('description');
        $groupType = ValueObjectFactory::createGroupType(GROUP_TYPE::GROUP);
        $image = ValueObjectFactory::createGroupImage($this->createMock(UploadedFileInterface::class));
        $input = new GroupCreateInputDto($userId, $groupName->getValue(), $description->getValue(), $groupType->getValue()->value, $image->getValue(), false);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->groupCreateService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupCreateDto $input) use ($userId, $groupName, $description, $groupType, $image): bool {
                $this->assertEquals($userId, $input->userCreatorId);
                $this->assertEquals($groupName, $input->name);
                $this->assertEquals($description, $input->description);
                $this->assertEquals($groupType, $input->type);
                $this->assertEquals($image, $input->image);

                return true;
            }))
            ->willReturn($this->group);

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->group
            ->expects($this->never())
            ->method('getName');

        $this->group
            ->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($groupId, $return);
    }

    #[Test]
    public function itShouldFailCreateTheGroupNotificationError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $groupName = ValueObjectFactory::createNameWithSpaces('group name');
        $description = ValueObjectFactory::createDescription('description');
        $groupType = ValueObjectFactory::createGroupType(GROUP_TYPE::GROUP);
        $image = ValueObjectFactory::createGroupImage($this->createMock(UploadedFileInterface::class));
        $input = new GroupCreateInputDto($userId, $groupName->getValue(), $description->getValue(), $groupType->getValue()->value, $image->getValue(), true);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->groupCreateService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (GroupCreateDto $input) use ($userId, $groupName, $description, $groupType, $image): bool {
                $this->assertEquals($userId, $input->userCreatorId);
                $this->assertEquals($groupName, $input->name);
                $this->assertEquals($description, $input->description);
                $this->assertEquals($groupType, $input->type);
                $this->assertEquals($image, $input->image);

                return true;
            }))
            ->willReturn($this->group);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId, $groupName): bool {
                $this->assertEquals([$userId->getValue()], $notificationDto->content['users_id']);
                $this->assertEquals($groupName->getValue(), $notificationDto->content['notification_data']['group_name']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::GROUP_CREATED->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->group
            ->expects($this->once())
            ->method('getName')
            ->willReturn($groupName);

        $this->group
            ->expects($this->never())
            ->method('getId');

        $this->expectException(GroupCreateNotificationException::class);
        $this->object->__invoke($input);
    }
}

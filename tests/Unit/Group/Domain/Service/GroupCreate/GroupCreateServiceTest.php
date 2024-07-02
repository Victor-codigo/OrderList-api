<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;
use Group\Domain\Service\GroupCreate\Exception\GroupCreateUserGroupTypeAlreadyExitsException;
use Group\Domain\Service\GroupCreate\GroupCreateService;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class GroupCreateServiceTest extends DataBaseTestCase
{
    private const string IMAGE_UPLOADED_FILE_NAME = 'Image.png';
    private const string PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';

    private GroupCreateService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|UploadedFileInterface $imageUploaded;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->imageUploaded = $this->createMock(UploadedFileInterface::class);
        $this->object = new GroupCreateService($this->groupRepository, $this->userGroupRepository, $this->fileUpload, self::PATH_IMAGE_UPLOAD);
    }

    private function createGroupCreateDto(MockObject|UploadedFileInterface|null $imageUploaded, GROUP_TYPE $groupType): GroupCreateDto
    {
        return new GroupCreateDto(
            ValueObjectFactory::createIdentifier('87c635dd-1861-430e-bbf8-9f21ac4b1b86'),
            ValueObjectFactory::createNameWithSpaces('GroupName'),
            ValueObjectFactory::createDescription('This is a description of the group'),
            ValueObjectFactory::createGroupType($groupType),
            ValueObjectFactory::createGroupImage($imageUploaded)
        );
    }

    private function assertGroupIsCreated(Group $group, GroupCreateDto $groupCreateDto, GROUP_TYPE $expectedGroupType, ?string $expectedImageUploadedFileName): void
    {
        $this->assertInstanceOf(Identifier::class, $group->getId());
        $this->assertSame($groupCreateDto->name, $group->getName());
        $this->assertSame($groupCreateDto->description, $group->getDescription());
        $this->assertEquals($expectedImageUploadedFileName, $group->getImage()->getValue());
        $this->assertEquals(ValueObjectFactory::createGroupType($expectedGroupType), $group->getType());
        $this->assertInstanceOf(\DateTime::class, $group->getCreatedOn());

        $userGroupCollection = $group->getUsers();
        $this->assertCount(1, $userGroupCollection);
        $this->assertContainsOnlyInstancesOf(UserGroup::class, $userGroupCollection);
        /** @var UserGroup $userGroup */
        $userGroup = $userGroupCollection->get(0);
        $this->assertEquals($groupCreateDto->userCreatorId, $userGroup->getUserId());
        $this->assertEquals($group->getId(), $userGroup->getGroupId());
        $this->assertEquals(
            ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::ADMIN)]),
            $userGroup->getRoles()
        );
    }

    /** @test */
    public function itShouldCreateTheGroupTypeUser(): void
    {
        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::USER);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($groupCreateDto->userCreatorId)
            ->willThrowException(new DBNotFoundException());

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto, GROUP_TYPE::USER, self::IMAGE_UPLOADED_FILE_NAME) || true));

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_IMAGE_UPLOAD);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $return = $this->object->__invoke($groupCreateDto);

        $this->assertInstanceOf(Group::class, $return);
        $this->assertSame($groupCreateDto->name, $return->getName());
        $this->assertSame($groupCreateDto->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
        $this->assertEquals(ValueObjectFactory::createGroupType(GROUP_TYPE::USER), $return->getType());
    }

    /** @test */
    public function itShouldCreateTheGroupTypeGroup(): void
    {
        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::GROUP);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findUserGroupsById');

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto, GROUP_TYPE::GROUP, self::IMAGE_UPLOADED_FILE_NAME) || true));

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_IMAGE_UPLOAD);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $return = $this->object->__invoke($groupCreateDto);

        $this->assertInstanceOf(Group::class, $return);
        $this->assertSame($groupCreateDto->name, $return->getName());
        $this->assertSame($groupCreateDto->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
        $this->assertEquals(ValueObjectFactory::createGroupType(GROUP_TYPE::GROUP), $return->getType());
    }

    /** @test */
    public function itShouldCreateTheGroupImageIsNull(): void
    {
        $groupCreateDto = $this->createGroupCreateDto(null, GROUP_TYPE::GROUP);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findUserGroupsById');

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto, GROUP_TYPE::GROUP, null) || true));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $return = $this->object->__invoke($groupCreateDto);

        $this->assertInstanceOf(Group::class, $return);
        $this->assertSame($groupCreateDto->name, $return->getName());
        $this->assertSame($groupCreateDto->description, $return->getDescription());
        $this->assertEquals(null, $return->getImage()->getValue());
        $this->assertEquals(ValueObjectFactory::createGroupType(GROUP_TYPE::GROUP), $return->getType());
    }

    /** @test */
    public function itShouldFailIdIsAlreadyRegistered(): void
    {
        $this->expectException(DBUniqueConstraintException::class);

        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::GROUP);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findUserGroupsById');

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_IMAGE_UPLOAD);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto, GROUP_TYPE::GROUP, self::IMAGE_UPLOADED_FILE_NAME) || true))
            ->willThrowException(new DBUniqueConstraintException());

        $this->object->__invoke($groupCreateDto);
    }

    /** @test */
    public function itShouldFailDatabaseConnectionException(): void
    {
        $this->expectException(DBConnectionException::class);

        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::GROUP);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findUserGroupsById');

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_IMAGE_UPLOAD);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto, GROUP_TYPE::GROUP, self::IMAGE_UPLOADED_FILE_NAME) || true))
            ->willThrowException(new DBConnectionException());

        $this->object->__invoke($groupCreateDto);
    }

    /** @test */
    public function itShouldFailUploadException(): void
    {
        $this->expectException(DBConnectionException::class);

        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::GROUP);

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findUserGroupsById');

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_IMAGE_UPLOAD)
            ->willThrowException(new FileUploadCanNotWriteException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(FileUploadCanNotWriteException::class);
        $this->object->__invoke($groupCreateDto);
    }

    /** @test */
    public function itShouldFailTheUserAlreadyHaveAUserGroup(): void
    {
        $groupCreateDto = $this->createGroupCreateDto($this->imageUploaded, GROUP_TYPE::USER);
        $paginator = $this->createMock(PaginatorInterface::class);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsById')
            ->with($groupCreateDto->userCreatorId)
            ->willReturn($paginator);

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->expectException(GroupCreateUserGroupTypeAlreadyExitsException::class);
        $this->object->__invoke($groupCreateDto);
    }
}

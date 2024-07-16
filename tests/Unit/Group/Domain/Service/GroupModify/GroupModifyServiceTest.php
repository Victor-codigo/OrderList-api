<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\FileSystem\DomainFileNotDeletedException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupModify\BuiltInFunctionsReturn;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;
use Group\Domain\Service\GroupModify\Exception\GroupModifyPermissionsException;
use Group\Domain\Service\GroupModify\GroupModifyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once 'tests/BuiltinFunctions/GroupApplicationGroupModify.php';
class GroupModifyServiceTest extends TestCase
{
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string PATH_UPLOAD_GROUP_IMAGE = 'path/uploaded/images';
    private const string FILE_NAME_FILE_UPLOADED = 'image.png';
    private const string FILE_NAME_FILE_UPLOADED_MODIFIED = 'imageModified.png';

    private GroupModifyService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|UploadedFileInterface $imageUploaded;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->imageUploaded = $this->createMock(UploadedFileInterface::class);
        $this->object = new GroupModifyService($this->groupRepository, $this->fileUpload, self::PATH_UPLOAD_GROUP_IMAGE);
    }

    #[\Override]
    protected function tearDown(): void
    {
        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    private function getGroup(): Group
    {
        return Group::fromPrimitives(self::GROUP_ID, 'groupName', GROUP_TYPE::GROUP, 'group description', self::FILE_NAME_FILE_UPLOADED);
    }

    private function getGroupModified(Group $group): Group
    {
        return (clone $group)
            ->setName(ValueObjectFactory::createNameWithSpaces('groupNameModified'))
            ->setDescription(ValueObjectFactory::createDescription('group description modified'))
            ->setImage(ValueObjectFactory::createPath(self::FILE_NAME_FILE_UPLOADED_MODIFIED));
    }

    /** @test */
    public function itShouldModifyTheGroup(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::FILE_NAME_FILE_UPLOADED_MODIFIED);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return true;
        };
        BuiltInFunctionsReturn::$unlink = BuiltInFunctionsReturn::$file_exists;
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheGroupFormerImageIsNull(): void
    {
        $group = $this->getGroup();
        $group->setImage(ValueObjectFactory::createPath(null));
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::FILE_NAME_FILE_UPLOADED_MODIFIED);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheGroupImageDoesNotExists(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::FILE_NAME_FILE_UPLOADED_MODIFIED);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return false;
        };

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheGroupImageIsNullAndRemoveImageIsFalse(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $groupModified->setImage($group->getImage());
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage(null)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheGroupImageIsNullAndRemoveImageIsTrue(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $groupModified->setImage(ValueObjectFactory::createPath(null));
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            true,
            ValueObjectFactory::createGroupImage(null)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return true;
        };
        BuiltInFunctionsReturn::$unlink = BuiltInFunctionsReturn::$file_exists;

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheGroupImageIsNotNullAndRemoveImageIsTrue(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            true,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::FILE_NAME_FILE_UPLOADED_MODIFIED);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return true;
        };
        BuiltInFunctionsReturn::$unlink = BuiltInFunctionsReturn::$file_exists;
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willThrowException(new DBNotFoundException());

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNoPermissions(): void
    {
        $group = $this->getGroup();
        $group->setType(ValueObjectFactory::createGroupType(GROUP_TYPE::USER));
        $input = new GroupModifyDto(
            $group->getId(),
            $group->getName(),
            $group->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$group->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(GroupModifyPermissionsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailConnectionOnSave(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::FILE_NAME_FILE_UPLOADED_MODIFIED);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified)
            ->willThrowException(new DBConnectionException());

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return true;
        };
        BuiltInFunctionsReturn::$unlink = BuiltInFunctionsReturn::$file_exists;

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailFileUploadException(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE)
            ->willThrowException(new FileUploadException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(FileUploadException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailFormerGroupImageCanNotBeDeleted(): void
    {
        $group = $this->getGroup();
        $groupImage = $group->getImage()->getValue();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription(),
            false,
            ValueObjectFactory::createGroupImage($this->imageUploaded)
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->imageUploaded, self::PATH_UPLOAD_GROUP_IMAGE);

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        BuiltInFunctionsReturn::$file_exists = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return true;
        };

        BuiltInFunctionsReturn::$unlink = function (string $fileName) use ($groupImage): bool {
            $this->assertEquals(
                self::PATH_UPLOAD_GROUP_IMAGE.'/'.$groupImage,
                $fileName
            );

            return false;
        };

        $this->expectException(DomainFileNotDeletedException::class);
        $this->object->__invoke($input);
    }
}

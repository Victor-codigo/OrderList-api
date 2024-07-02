<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Service\Image\UploadImage;

use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Image\Exception\ImageResizeException;
use Common\Domain\Model\ValueObject\Object\ObjectValueObject;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Common\Domain\Service\Image\EntityImageModifyInterface;
use Common\Domain\Service\Image\UploadImage\BuiltInFunctionsReturn;
use Common\Domain\Service\Image\UploadImage\Dto\UploadImageDto;
use Common\Domain\Service\Image\UploadImage\UploadImageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once 'tests/BuiltinFunctions/UploadImageService.php';

class UploadImageServiceTest extends TestCase
{
    private const string IMAGES_PATH = 'path/to/images';

    private UploadImageService $object;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|EntityImageModifyInterface $entity;
    private MockObject|ObjectValueObject $imageUploaded;
    private MockObject|UploadedFileInterface $uploadedFile;
    private MockObject|ImageInterface $image;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->entity = $this->createMock(EntityImageModifyInterface::class);
        $this->imageUploaded = $this->createMock(ObjectValueObject::class);
        $this->uploadedFile = $this->createMock(UploadedFileInterface::class);
        $this->image = $this->createMock(ImageInterface::class);
        $this->object = new UploadImageService($this->fileUpload, $this->image);
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    /** @test */
    public function itShouldModifyTheEntityImage(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $entityImageFileNameNew = ValueObjectFactory::createPath('entity image file new name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with($entityImageFileNameNew);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->uploadedFile, $input->imagesPathToStore->getValue(), $entityImageFileName->getValue());

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($entityImageFileNameNew->getValue());

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath("{$input->imagesPathToStore->getValue()}/{$entityImageFileNameNew->getValue()}"),
                300,
                300
            );

        $return = $this->object->__invoke($input);

        $this->assertEquals($entityImageFileNameNew, $return);
    }

    /** @test */
    public function itShouldModifyTheEntityImageNoResizeWidthIsNull(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $entityImageFileNameNew = ValueObjectFactory::createPath('entity image file new name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            null,
            300
        );

        $this->imageUploaded
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with($entityImageFileNameNew);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->uploadedFile, $input->imagesPathToStore->getValue(), $entityImageFileName->getValue());

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($entityImageFileNameNew->getValue());

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $return = $this->object->__invoke($input);

        $this->assertEquals($entityImageFileNameNew, $return);
    }

    /** @test */
    public function itShouldModifyTheEntityImageNoResizeHeightIsNull(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $entityImageFileNameNew = ValueObjectFactory::createPath('entity image file new name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            300,
            null
        );

        $this->imageUploaded
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with($entityImageFileNameNew);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->uploadedFile, $input->imagesPathToStore->getValue(), $entityImageFileName->getValue());

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($entityImageFileNameNew->getValue());

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $return = $this->object->__invoke($input);

        $this->assertEquals($entityImageFileNameNew, $return);
    }

    /** @test */
    public function itShouldNotModifyTheEntityImageSetToNull(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->never())
            ->method('getValue');

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(true);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $return = $this->object->__invoke($input);

        $this->assertEquals($entityImageFileName, $return);
    }

    /** @test */
    public function itShouldModifyTheEntityImageRemove(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            null,
            true,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->never())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->never())
            ->method('isNull');

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with(new Path(null));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $return = $this->object->__invoke($input);

        $this->assertNull($return->getValue());
    }

    /** @test */
    public function itShouldModifyTheEntityImageRemoveImageEntityIsNull(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath(null);
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            null,
            true,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->never())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->never())
            ->method('isNull');

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with(new Path(null));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $return = $this->object->__invoke($input);

        $this->assertNull($return->getValue());
    }

    /** @test */
    public function itShouldModifyTheEntityImageRemoveImageEntityNotExists(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('Entity image file name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            null,
            true,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->never())
            ->method('getValue');

        $this->imageUploaded
            ->expects($this->never())
            ->method('isNull');

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with(new Path(null));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        BuiltInFunctionsReturn::$file_exists = false;
        $return = $this->object->__invoke($input);

        $this->assertNull($return->getValue());
    }

    /** @test */
    public function itShouldFailModifyingTheEntityImageRemoveImageCanNotBeRemoved(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('Entity image file name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            null,
            true,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->never())
            ->method('getValue');

        $this->imageUploaded
            ->expects($this->never())
            ->method('isNull');

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;

        $this->expectException(FileUploadReplaceException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheEntityImageUploadFileError(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->uploadedFile, $input->imagesPathToStore->getValue(), $entityImageFileName->getValue())
            ->willThrowException(new FileException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->expectException(FileException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldResizingTheImage(): void
    {
        $entityImageFileName = ValueObjectFactory::createPath('entity image file name');
        $entityImageFileNameNew = ValueObjectFactory::createPath('entity image file new name');
        $input = new UploadImageDto(
            $this->entity,
            ValueObjectFactory::createPath(self::IMAGES_PATH),
            $this->imageUploaded,
            false,
            300,
            300
        );

        $this->imageUploaded
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($this->uploadedFile);

        $this->imageUploaded
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($entityImageFileName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->uploadedFile, $input->imagesPathToStore->getValue(), $entityImageFileName->getValue());

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($entityImageFileNameNew->getValue());

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath("{$input->imagesPathToStore->getValue()}/{$entityImageFileNameNew->getValue()}"),
                300,
                300
            )
            ->willThrowException(new ImageResizeException());

        $this->expectException(ImageResizeException::class);
        $return = $this->object->__invoke($input);

        $this->assertEquals($entityImageFileNameNew, $return);
    }
}

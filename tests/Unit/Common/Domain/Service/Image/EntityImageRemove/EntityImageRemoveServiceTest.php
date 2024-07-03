<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Service\Image\EntityImageRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageModifyInterface;
use Common\Domain\Service\Image\EntityImageRemove\BuiltInFunctionsReturn;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once 'tests/BuiltinFunctions/EntityImageRemoveService.php';

class EntityImageRemoveServiceTest extends TestCase
{
    private EntityImageRemoveService $object;
    private MockObject|EntityImageModifyInterface $entity;
    private MockObject|Path $imagesPathToStorage;
    private MockObject|Path $entityImageName;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = $this->createMock(EntityImageModifyInterface::class);
        $this->imagesPathToStorage = $this->createMock(Path::class);
        $this->entityImageName = $this->createMock(Path::class);
        $this->object = new EntityImageRemoveService();
    }

    /** @test */
    public function ittShouldDoNothingEntityImageIsNull(): void
    {
        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($this->entityImageName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->entityImageName
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(true);

        $this->imagesPathToStorage
            ->expects($this->never())
            ->method('isNull');

        $this->object->__invoke($this->entity, $this->imagesPathToStorage);
    }

    /** @test */
    public function itShouldDoNothingImagesPathToStorageIsNull(): void
    {
        $this->entity
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($this->entityImageName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->entityImageName
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->imagesPathToStorage
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(true);

        $this->object->__invoke($this->entity, $this->imagesPathToStorage);
    }

    /** @test */
    public function itShouldSetEntityImageToNullAndNotRemoveImageFileNotExists(): void
    {
        $this->entity
            ->expects($this->exactly(2))
            ->method('getImage')
            ->willReturn($this->entityImageName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with(ValueObjectFactory::createPath(null));

        $this->entityImageName
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->imagesPathToStorage
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        BuiltInFunctionsReturn::$file_exists = false;
        $this->object->__invoke($this->entity, $this->imagesPathToStorage);
    }

    /** @test */
    public function itShouldSetEntityImageToNullAndNotRemoveImageFileCanNotBeRemoved(): void
    {
        $this->entity
            ->expects($this->exactly(2))
            ->method('getImage')
            ->willReturn($this->entityImageName);

        $this->entity
            ->expects($this->never())
            ->method('setImage');

        $this->entityImageName
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->imagesPathToStorage
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($this->entity, $this->imagesPathToStorage);
    }

    /** @test */
    public function itShouldSetEntityImageToNullAndRemoveImageFile(): void
    {
        $this->entity
            ->expects($this->exactly(2))
            ->method('getImage')
            ->willReturn($this->entityImageName);

        $this->entity
            ->expects($this->once())
            ->method('setImage')
            ->with(ValueObjectFactory::createPath(null));

        $this->entityImageName
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        $this->imagesPathToStorage
            ->expects($this->once())
            ->method('isNull')
            ->willReturn(false);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $this->object->__invoke($this->entity, $this->imagesPathToStorage);
    }
}

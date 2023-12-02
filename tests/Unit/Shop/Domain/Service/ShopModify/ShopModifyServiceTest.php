<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopModify\BuiltInFunctionsReturn;
use Shop\Domain\Service\ShopModify\Dto\ShopModifyDto;
use Shop\Domain\Service\ShopModify\Exception\ShopModifyNameIsAlreadyInDataBaseException;
use Shop\Domain\Service\ShopModify\ShopModifyService;

require_once 'tests/BuiltinFunctions/ShopModifyService.php';

class ShopModifyServiceTest extends TestCase
{
    private const SHOP_ID = 'shop id';
    private const GROUP_ID = 'group id';
    private const SHOP_IMAGE_PATH = 'path\to\shops\images';

    private ShopModifyService $object;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|UploadedFileInterface $shopImage;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->shopImage = $this->createMock(UploadedFileInterface::class);
        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShopModifyService($this->shopRepository, $this->fileUpload, self::SHOP_IMAGE_PATH);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    private function getShop(): Shop
    {
        return Shop::fromPrimitives(
            self::SHOP_ID,
            self::GROUP_ID,
            'shop name db',
            'shop description db',
            'shop image path db'
        );
    }

    /** @test */
    public function itShouldModifyTheShopImageAndDescription(): void
    {
        $shopFromDb = $this->getShop();
        $fileUploadedName = 'file_uploaded_name';
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $shopFromDb->getName(),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage($this->shopImage),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($shopFromDb->getName())
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath($fileUploadedName));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($input->image->getValue(), self::SHOP_IMAGE_PATH, $shopFromDb->getImage()->getValue());

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileUploadedName);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheShopNameImageAndDescription(): void
    {
        $shopFromDb = $this->getShop();
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage(null),
            false
        );
        $shopExpected = (clone $shopFromDb)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setImage($shopFromDb->getImage());

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheShopNameDescriptionImageRemoved(): void
    {
        $shopFromDb = $this->getShop();
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage(null),
            true
        );
        $shopExpected = (clone $shopFromDb)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath(null));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheShopNameDescriptionImageRemovedNoImageSetToTheShop(): void
    {
        $shopFromDb = $this->getShop();
        $shopFromDb->setImage(ValueObjectFactory::createPath(null));
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage(null),
            true
        );
        $shopExpected = (clone $shopFromDb)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath(null));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheShopNameDescriptionImageNotExists(): void
    {
        $shopFromDb = $this->getShop();
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage(null),
            true
        );
        $shopExpected = (clone $shopFromDb)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath(null));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        BuiltInFunctionsReturn::$file_exists = false;
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyShopName(): void
    {
        $shopFromDb = $this->getShop();
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription(null),
            ValueObjectFactory::createShopImage(null),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($input->name)
            ->setDescription($shopFromDb->getDescription())
            ->setImage($shopFromDb->getImage());

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($shopExpected);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheShopShopNotBelongToTheGroup(): void
    {
        $shopFromDb = $this->getShop();
        $fileUploadedName = 'file_uploaded_name';
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier('396e0152-d501-45d9-bf58-7498e11ea6c5'),
            $shopFromDb->getName(),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage($this->shopImage),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($shopFromDb->getName())
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath($fileUploadedName));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheShopShopNameNotFound(): void
    {
        $shopFromDb = $this->getShop();
        $fileUploadedName = 'file_uploaded_name';
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $shopFromDb->getName(),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage($this->shopImage),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($shopFromDb->getName())
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath($fileUploadedName));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailNameIsAlreadyInUse(): void
    {
        $shopFromDb = $this->getShop();
        $fileUploadedName = 'file_uploaded_name';
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop`name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage($this->shopImage),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($shopFromDb->getName())
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath($fileUploadedName));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->expectException(ShopModifyNameIsAlreadyInDataBaseException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailFileUploadException(): void
    {
        $shopFromDb = $this->getShop();
        $fileUploadedName = 'file_uploaded_name';
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $shopFromDb->getName(),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage($this->shopImage),
            false
        );
        $shopExpected = clone $shopFromDb;
        $shopExpected
            ->setName($shopFromDb->getName())
            ->setDescription($input->description)
            ->setImage(ValueObjectFactory::createPath($fileUploadedName));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($input->image->getValue(), self::SHOP_IMAGE_PATH, $shopFromDb->getImage()->getValue())
            ->willThrowException(new FileException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->expectException(FileException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailErrorRemovingImage(): void
    {
        $shopFromDb = $this->getShop();
        $input = new ShopModifyDto(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createShopImage(null),
            true
        );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$shopFromDb]));

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->expectException(FileUploadReplaceException::class);
        $this->object->__invoke($input);
    }
}

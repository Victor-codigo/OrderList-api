<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Image\Exception\ImageResizeException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopCreate\Dto\ShopCreateDto;
use Shop\Domain\Service\ShopCreate\Exception\ShopCreateNameAlreadyExistsException;
use Shop\Domain\Service\ShopCreate\ShopCreateService;

class ShopCreateServiceTest extends TestCase
{
    private const IMAGE_UPLOADED_FILE_NAME = 'Image.png';
    private const IMAGE_UPLOADED_PATH = '/uploaded/image/path';
    private const GROUP_ID = '82633054-84ad-4748-8ea2-8be0201c7b3a';

    private ShopCreateService $object;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|UploadedFileInterface $shopImageFile;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|ImageInterface $image;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->shopImageFile = $this->createMock(UploadedFileInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->image = $this->createMock(ImageInterface::class);
        $this->object = new ShopCreateService($this->shopRepository, $this->fileUpload, $this->image, self::IMAGE_UPLOADED_PATH);
    }

    private function createShopCreateDto(?string $description, ?string $address, ?FileInterface $shopImageFile): ShopCreateDto
    {
        return new ShopCreateDto(
            ValueObjectFactory::createIdentifier('276865ee-d120-46e9-a3f7-16f7c923a990'),
            ValueObjectFactory::createNameWithSpaces('shop 1'),
            ValueObjectFactory::createAddress($address),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createShopImage($shopImageFile),
        );
    }

    private function assertShopIsCreated(Shop $shop, ShopCreateDto $input, ?string $expectedImageShopName): bool
    {
        $this->assertEquals(self::GROUP_ID, $shop->getId());
        $this->assertEquals($input->groupId, $shop->getGroupId());
        $this->assertEquals($input->name, $shop->getName());
        $this->assertEquals($input->description, $shop->getDescription());
        $this->assertEquals($expectedImageShopName, $shop->getImage()->getValue());

        return true;
    }

    /** @test */
    public function itShouldCreateAShopAllData(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH);

        $this->fileUpload
            ->expects($this->exactly(2))
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath(self::IMAGE_UPLOADED_PATH.'/'.self::IMAGE_UPLOADED_FILE_NAME),
                300,
                300
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Shop $shop) => $this->assertShopIsCreated($shop, $input, self::IMAGE_UPLOADED_FILE_NAME)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->address, $return->getAddress());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
    }

    /** @test */
    public function itShouldCreateAShopDescriptionIsNull(): void
    {
        $shopDescription = null;
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH);

        $this->fileUpload
            ->expects($this->exactly(2))
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath(self::IMAGE_UPLOADED_PATH.'/'.self::IMAGE_UPLOADED_FILE_NAME),
                300,
                300
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Shop $shop) => $this->assertShopIsCreated($shop, $input, self::IMAGE_UPLOADED_FILE_NAME)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->address, $return->getAddress());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
    }

    /** @test */
    public function itShouldCreateAShopAddressIsNull(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = null;
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH);

        $this->fileUpload
            ->expects($this->exactly(2))
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath(self::IMAGE_UPLOADED_PATH.'/'.self::IMAGE_UPLOADED_FILE_NAME),
                300,
                300
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Shop $shop) => $this->assertShopIsCreated($shop, $input, self::IMAGE_UPLOADED_FILE_NAME)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->address, $return->getAddress());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
    }

    /** @test */
    public function itShouldCreateAShopImageIsNull(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, null);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Shop $shop) => $this->assertShopIsCreated($shop, $input, null)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->address, $return->getAddress());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertNull($return->getImage()->getValue());
    }

    /** @test */
    public function itShouldFailShopNameAlreadyExists(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('generateId');

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ShopCreateNameAlreadyExistsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailFileUploadException(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH)
            ->willThrowException(new FileUploadException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(FileUploadException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailResizeFileUploadedException(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH);

        $this->fileUpload
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath(self::IMAGE_UPLOADED_PATH.'/'.self::IMAGE_UPLOADED_FILE_NAME),
                300,
                300
            )
            ->willThrowException(new ImageResizeException());

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ImageResizeException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailSaveError(): void
    {
        $shopDescription = 'shop 1 description';
        $shopAddress = 'Shop address';
        $input = $this->createShopCreateDto($shopDescription, $shopAddress, $this->shopImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->shopImageFile, self::IMAGE_UPLOADED_PATH);

        $this->fileUpload
            ->expects($this->exactly(2))
            ->method('getFileName')
            ->willReturn(self::IMAGE_UPLOADED_FILE_NAME);

        $this->image
            ->expects($this->once())
            ->method('resizeToAFrame')
            ->with(
                ValueObjectFactory::createPath(self::IMAGE_UPLOADED_PATH.'/'.self::IMAGE_UPLOADED_FILE_NAME),
                300,
                300
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with($input->groupId, $input->name, true)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->shopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Shop $shop) => $this->assertShopIsCreated($shop, $input, self::IMAGE_UPLOADED_FILE_NAME)))
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }
}

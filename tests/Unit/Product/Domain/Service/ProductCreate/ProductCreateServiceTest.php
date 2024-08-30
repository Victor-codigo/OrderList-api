<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductCreate;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductCreate\Dto\ProductCreateDto;
use Product\Domain\Service\ProductCreate\Exception\ProductCreateNameAlreadyExistsException;
use Product\Domain\Service\ProductCreate\ProductCreateService;

class ProductCreateServiceTest extends TestCase
{
    private const string IMAGE_UPLOADED_FILE_NAME = 'Image.png';
    private const string IMAGE_UPLOADED_PATH = '/uploaded/image/path';
    private const string GROUP_ID = '82633054-84ad-4748-8ea2-8be0201c7b3a';

    private ProductCreateService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|FileUploadInterface $fileUpload;
    private MockObject|UploadedFileInterface $productImageFile;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|ImageInterface $image;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->fileUpload = $this->createMock(FileUploadInterface::class);
        $this->productImageFile = $this->createMock(UploadedFileInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->image = $this->createMock(ImageInterface::class);
        $this->object = new ProductCreateService($this->productRepository, $this->fileUpload, $this->image, self::IMAGE_UPLOADED_PATH);
    }

    private function createProductCreateDto(?string $description, MockObject|UploadedFileInterface|null $productImageFile): ProductCreateDto
    {
        return new ProductCreateDto(
            ValueObjectFactory::createIdentifier('276865ee-d120-46e9-a3f7-16f7c923a990'),
            ValueObjectFactory::createNameWithSpaces('product 1'),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createProductImage($productImageFile),
        );
    }

    private function assertProductIsCreated(Product $product, ProductCreateDto $input, ?string $expectedImageProductName): bool
    {
        $this->assertEquals(self::GROUP_ID, $product->getId());
        $this->assertEquals($input->groupId, $product->getGroupId());
        $this->assertEquals($input->name, $product->getName());
        $this->assertEquals($input->description, $product->getDescription());
        $this->assertEquals($expectedImageProductName, $product->getImage()->getValue());

        return true;
    }

    #[Test]
    public function itShouldCreateAProductAllData(): void
    {
        $productDescription = 'product 1 description';
        $input = $this->createProductCreateDto($productDescription, $this->productImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->productImageFile, self::IMAGE_UPLOADED_PATH);

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

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Product $product): bool => $this->assertProductIsCreated($product, $input, self::IMAGE_UPLOADED_FILE_NAME)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAProductDescriptionIsNull(): void
    {
        $productDescription = null;
        $input = $this->createProductCreateDto($productDescription, $this->productImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->productImageFile, self::IMAGE_UPLOADED_PATH);

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

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Product $product): bool => $this->assertProductIsCreated($product, $input, self::IMAGE_UPLOADED_FILE_NAME)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertEquals(self::IMAGE_UPLOADED_FILE_NAME, $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAProductImageIsNull(): void
    {
        $productDescription = 'product 1 description';
        $input = $this->createProductCreateDto($productDescription, null);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Product $product): bool => $this->assertProductIsCreated($product, $input, null)));

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::GROUP_ID, $return->getId());
        $this->assertEquals($input->groupId, $return->getGroupId());
        $this->assertEquals($input->name, $return->getName());
        $this->assertEquals($input->description, $return->getDescription());
        $this->assertNull($return->getImage()->getValue());
    }

    #[Test]
    public function itShouldFailProductNameAlreadyExists(): void
    {
        $productDescription = 'product 1 description';
        $input = $this->createProductCreateDto($productDescription, $this->productImageFile);

        $this->fileUpload
            ->expects($this->never())
            ->method('__invoke');

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('generateId');

        $this->productRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ProductCreateNameAlreadyExistsException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailFileUploadException(): void
    {
        $productDescription = 'product 1 description';
        $input = $this->createProductCreateDto($productDescription, $this->productImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->productImageFile, self::IMAGE_UPLOADED_PATH)
            ->willThrowException(new FileUploadException());

        $this->fileUpload
            ->expects($this->never())
            ->method('getFileName');

        $this->image
            ->expects($this->never())
            ->method('resizeToAFrame');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->productRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(FileUploadException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailSaveError(): void
    {
        $productDescription = 'product 1 description';
        $input = $this->createProductCreateDto($productDescription, $this->productImageFile);

        $this->fileUpload
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->productImageFile, self::IMAGE_UPLOADED_PATH);

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

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('generateId')
            ->willReturn(self::GROUP_ID);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Product $product): bool => $this->assertProductIsCreated($product, $input, self::IMAGE_UPLOADED_FILE_NAME)))
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }
}

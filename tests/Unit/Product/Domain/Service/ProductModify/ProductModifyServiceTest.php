<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Service\Image\UploadImage\Dto\UploadImageDto;
use Common\Domain\Service\Image\UploadImage\UploadImageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductModify\Dto\ProductModifyDto;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNameIsAlreadyInDataBaseException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNotFoundException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductShopException;
use Product\Domain\Service\ProductModify\ProductModifyService;

require_once 'tests/BuiltinFunctions/ProductModifyService.php';

class ProductModifyServiceTest extends TestCase
{
    private const PRODUCT_ID = 'product id';
    private const GROUP_ID = 'group id';
    private const PRODUCT_IMAGE_PATH = 'path\to\products\images';

    private ProductModifyService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|UploadImageService $uploadImageService;
    private MockObject|PaginatorInterface $productsPaginator;
    private MockObject|ProductImage $productImage;
    private MockObject|Product $productFromDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->uploadImageService = $this->createMock(UploadImageService::class);
        $this->productsPaginator = $this->createMock(PaginatorInterface::class);
        $this->productImage = $this->createMock(ProductImage::class);
        $this->productFromDb = $this->createMock(Product::class);
        $this->object = new ProductModifyService(
            $this->productRepository,
            $this->uploadImageService,
            self::PRODUCT_IMAGE_PATH
        );
    }

    /** @test */
    public function itShouldFailProductNotFound(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            false
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByGroupAndNameOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('save');

        $this->productsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->productsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->productFromDb
            ->expects($this->never())
            ->method('setName');

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->productFromDb
            ->expects($this->never())
            ->method('getName');

        $this->uploadImageService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(ProductModifyProductNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyProductNameIsEqualToProducts(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            false
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByGroupAndNameOrFail');

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productFromDb);

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription')
            ->with($input->description);

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailProductNameRepeated(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            false
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->never())
            ->method('save');

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productFromDb
            ->expects($this->never())
            ->method('setName');

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ValueObjectFactory::createNameWithSpaces('Other product name'));

        $this->uploadImageService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(ProductModifyProductNameIsAlreadyInDataBaseException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheProductNameImageAndDescription(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            false
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productFromDb);

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ValueObjectFactory::createNameWithSpaces('Other product name'));

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription')
            ->with($input->description);

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyNothing(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createDescription(null),
            $this->productImage,
            false
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByGroupAndNameOrFail');

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productFromDb);

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->never())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->productFromDb
            ->expects($this->never())
            ->method('getName');

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingProductUploadServiceException(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            true
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->never())
            ->method('save');

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription');

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ValueObjectFactory::createNameWithSpaces('Other product name'));

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage)
            ->willThrowException(new FileException());

        $this->expectException(FileException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheProductNameDescriptionAndRemoveImage(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            true
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productFromDb);

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription')
            ->with($input->description);

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ValueObjectFactory::createNameWithSpaces('Other product name'));

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheProductNameDescriptionAndRemoveImageErrorOnSave(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            $this->productImage,
            true
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove,
            300,
            300
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByGroupAndNameOrFail')
            ->with($input->groupId, $input->name)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productFromDb)
            ->willThrowException(new DBConnectionException());

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productFromDb]));

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription')
            ->with($input->description);

        $this->productFromDb
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ValueObjectFactory::createNameWithSpaces('Other product name'));

        $this->uploadImageService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputUploadImage);

        $this->expectException(ProductModifyProductShopException::class);
        $this->object->__invoke($input);
    }
}

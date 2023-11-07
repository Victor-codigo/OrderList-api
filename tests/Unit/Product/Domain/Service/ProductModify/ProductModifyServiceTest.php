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
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNotFoundException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductShopException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyShopNotFoundException;
use Product\Domain\Service\ProductModify\ProductModifyService;
use Product\Domain\Service\ProductShop\Dto\ProductShopDto;
use Product\Domain\Service\ProductShop\ProductShopService;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

require_once 'tests/BuiltinFunctions/ProductModifyService.php';

class ProductModifyServiceTest extends TestCase
{
    private const PRODUCT_ID = 'product id';
    private const GROUP_ID = 'group id';
    private const SHOP_ID = 'shop id';
    private const PRODUCT_IMAGE_PATH = 'path\to\products\images';

    private ProductModifyService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|ProductShopService $productShopService;
    private MockObject|UploadImageService $uploadImageService;
    private MockObject|PaginatorInterface $productsPaginator;
    private MockObject|PaginatorInterface $shopsPaginator;
    private MockObject|ProductImage $productImage;
    private MockObject|Product $productFromDb;
    private MockObject|Shop $shopFromDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->productShopService = $this->createMock(ProductShopService::class);
        $this->uploadImageService = $this->createMock(UploadImageService::class);
        $this->productsPaginator = $this->createMock(PaginatorInterface::class);
        $this->shopsPaginator = $this->createMock(PaginatorInterface::class);
        $this->productImage = $this->createMock(ProductImage::class);
        $this->productFromDb = $this->createMock(Product::class);
        $this->shopFromDb = $this->createMock(Shop::class);
        $this->object = new ProductModifyService(
            $this->productRepository,
            $this->shopRepository,
            $this->productShopService,
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
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willThrowException(new DBNotFoundException());

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

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopService
            ->expects($this->never())
            ->method('__invoke');

        $this->productFromDb
            ->expects($this->never())
            ->method('setName');

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->uploadImageService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(ProductModifyProductNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheProductWithoutShopId(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(null),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

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

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopService
            ->expects($this->never())
            ->method('__invoke');

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

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
    public function itShouldFailModifyingTheProductShopIdIsNotFound(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
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

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willThrowException(new DBNotFoundException());

        $this->shopsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopService
            ->expects($this->never())
            ->method('__invoke');

        $this->productFromDb
            ->expects($this->never())
            ->method('setName');

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->uploadImageService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(ProductModifyShopNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheProductShopIdIsNull(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(null),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

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

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopService
            ->expects($this->never())
            ->method('__invoke');

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

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
    public function itShouldFailModifyingProductProductShopServiceException(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $inputProductShopService = new ProductShopDto(
            $this->productFromDb,
            $this->shopFromDb,
            $input->price,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
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

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->shopFromDb]));

        $this->productShopService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputProductShopService)
            ->willThrowException(new DBConnectionException());

        $this->productFromDb
            ->expects($this->never())
            ->method('setName');

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription');

        $this->uploadImageService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(ProductModifyProductShopException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldModifyTheProductNameImageAndDescription(): void
    {
        $input = new ProductModifyDto(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $inputProductShopService = new ProductShopDto(
            $this->productFromDb,
            $this->shopFromDb,
            $input->price,
            $input->imageRemove
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

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

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->shopFromDb]));

        $this->productShopService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputProductShopService);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

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
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createDescription(null),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            false
        );

        $inputProductShopService = new ProductShopDto(
            $this->productFromDb,
            $this->shopFromDb,
            $input->price,
            $input->imageRemove
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

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

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->shopFromDb]));

        $this->productShopService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputProductShopService);

        $this->productFromDb
            ->expects($this->never())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->never())
            ->method('setDescription')
            ->with($input->description);

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
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            true
        );

        $inputProductShopService = new ProductShopDto(
            $this->productFromDb,
            $this->shopFromDb,
            $input->price,
            $input->imageRemove
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
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

        $this->productsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->shopFromDb]));

        $this->productShopService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputProductShopService);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

        $this->productFromDb
            ->expects($this->once())
            ->method('setDescription')
            ->with($input->description);

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
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description modified'),
            ValueObjectFactory::createMoney(null),
            $this->productImage,
            true
        );

        $inputProductShopService = new ProductShopDto(
            $this->productFromDb,
            $this->shopFromDb,
            $input->price,
            $input->imageRemove
        );

        $inputUploadImage = new UploadImageDto(
            $this->productFromDb,
            ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH),
            $this->productImage,
            $input->imageRemove
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->willReturn($this->productsPaginator);

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

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->shopFromDb]));

        $this->productShopService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputProductShopService);

        $this->productFromDb
            ->expects($this->once())
            ->method('setName')
            ->with($input->name);

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
}

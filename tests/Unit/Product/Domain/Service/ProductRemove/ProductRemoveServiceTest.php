<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductRemove;

use Override;
use ArrayObject;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductRemove\BuiltInFunctionsReturn;
use Product\Domain\Service\ProductRemove\Dto\ProductRemoveDto;
use Product\Domain\Service\ProductRemove\ProductRemoveService;

require_once 'tests/BuiltinFunctions/ProductRemoveService.php';

class ProductRemoveServiceTest extends TestCase
{
    private const string PRODUCT_IMAGE_PATH = 'path/to/product/image';

    private ProductRemoveService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|EntityImageRemoveService $entityImageRemoveService;
    private MockObject|PaginatorInterface $paginator;
    private Path $productImagePath;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->entityImageRemoveService = $this->createMock(EntityImageRemoveService::class);
        $this->productImagePath = ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH);
        $this->object = new ProductRemoveService($this->productRepository, $this->entityImageRemoveService, $this->productImagePath->getValue());
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    private function getProduct(Identifier $groupId, Identifier $productId, Identifier $shopId, string $image = null): Product
    {
        return new Product(
            $productId,
            $groupId,
            ValueObjectFactory::createNameWithSpaces('product name'),
            ValueObjectFactory::createDescription('product description'),
            ValueObjectFactory::createPath($image)
        );
    }

    /** @test */
    public function itShouldRemoveAProduct(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [ValueObjectFactory::createIdentifier('product id')];
        $shopsId = [ValueObjectFactory::createIdentifier('shop id')];
        $products = [$this->getProduct($groupId, $productsId[0], $shopsId[0])];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayObject($products));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($products[0], $this->productImagePath);

        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($productsId, $return);
    }

    /** @test */
    public function itShouldRemoveManyProducts(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [
            ValueObjectFactory::createIdentifier('product1 id'),
            ValueObjectFactory::createIdentifier('product2 id'),
            ValueObjectFactory::createIdentifier('product3 id'),
        ];
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop1 id'),
            ValueObjectFactory::createIdentifier('shop2 id'),
            ValueObjectFactory::createIdentifier('shop3 id'),
        ];
        $products = [
            $this->getProduct($groupId, $productsId[0], $shopsId[0]),
            $this->getProduct($groupId, $productsId[1], $shopsId[1]),
            $this->getProduct($groupId, $productsId[2], $shopsId[2]),
        ];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayObject($products));

        $productImagePath = $this->productImagePath;
        $entityImageRemoveServiceMatcher = $this->exactly(3);
        $this->entityImageRemoveService
            ->expects($entityImageRemoveServiceMatcher)
            ->method('__invoke')
            ->willReturnCallback(function (Product $productToRemove, Path $productImagePathToRemove) use ($entityImageRemoveServiceMatcher, $products, $productImagePath) {
                $this->assertEquals($products[$entityImageRemoveServiceMatcher->getInvocationCount() - 1], $productToRemove);
                $this->assertEquals($productImagePath, $productImagePathToRemove);

                return true;
            });

        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($productsId, $return);
    }

    /** @test */
    public function itShouldRemoveAProductImageFileExists(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [ValueObjectFactory::createIdentifier('product id')];
        $shopsId = [ValueObjectFactory::createIdentifier('shop id')];
        $products = [$this->getProduct($groupId, $productsId[0], $shopsId[0], self::PRODUCT_IMAGE_PATH)];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayObject($products));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($products[0], $this->productImagePath);

        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $return = $this->object->__invoke($input);

        $this->assertEquals($productsId, $return);
    }

    /** @test */
    public function itShouldRemoveAProductImageFileNotExists(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [ValueObjectFactory::createIdentifier('product id')];
        $shopsId = [ValueObjectFactory::createIdentifier('shop id')];
        $products = [$this->getProduct($groupId, $productsId[0], $shopsId[0], self::PRODUCT_IMAGE_PATH)];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayObject($products));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($products[0], $this->productImagePath);

        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);

        $return = $this->object->__invoke($input);

        BuiltInFunctionsReturn::$file_exists = false;
        $this->assertEquals($productsId, $return);
    }

    /** @test */
    public function itShouldFailRemovingAProductImageFileRemoveException(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [ValueObjectFactory::createIdentifier('product id')];
        $shopsId = [ValueObjectFactory::createIdentifier('shop id')];
        $products = [$this->getProduct($groupId, $productsId[0], $shopsId[0], self::PRODUCT_IMAGE_PATH)];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayObject($products));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($products[0], $this->productImagePath)
            ->willThrowException(new DomainInternalErrorException());

        $this->expectException(DomainInternalErrorException::class);
        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingAProductProductNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [ValueObjectFactory::createIdentifier('product id')];
        $shopsId = [ValueObjectFactory::createIdentifier('shop id')];

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsId, $shopsId)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->entityImageRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $input = new ProductRemoveDto($groupId, $productsId, $shopsId);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}

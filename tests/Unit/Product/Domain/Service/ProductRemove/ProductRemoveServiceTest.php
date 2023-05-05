<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
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
    private const PRODUCT_IMAGE_PATH = 'path/to/product/image';

    private ProductRemoveService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ProductRemoveService($this->productRepository, self::PRODUCT_IMAGE_PATH);
    }

    private function getProduct(Identifier $productId, Identifier $groupId, Identifier $shopId, string $image = null): Product
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
        $productId = ValueObjectFactory::createIdentifier('product id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $product = $this->getProduct($productId, $groupId, $shopId);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with([$productId], $groupId, $shopId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$product]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$product]));

        $input = new ProductRemoveDto($productId, $groupId, $shopId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($productId, $return);
    }

    /** @test */
    public function itShouldRemoveAProductImageFileExists(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $product = $this->getProduct($productId, $groupId, $shopId, self::PRODUCT_IMAGE_PATH);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with([$productId], $groupId, $shopId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$product]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$product]));

        $input = new ProductRemoveDto($productId, $groupId, $shopId);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $return = $this->object->__invoke($input);

        $this->assertEquals($productId, $return);
    }

    /** @test */
    public function itShouldRemoveAProductImageFileNotExists(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $product = $this->getProduct($productId, $groupId, $shopId, self::PRODUCT_IMAGE_PATH);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with([$productId], $groupId, $shopId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$product]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$product]));

        $input = new ProductRemoveDto($productId, $groupId, $shopId);

        $return = $this->object->__invoke($input);

        BuiltInFunctionsReturn::$file_exists = false;
        $this->assertEquals($productId, $return);
    }

    /** @test */
    public function itShouldFailRemovingAProductProductNotFound(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with([$productId], $groupId, $shopId)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $input = new ProductRemoveDto($productId, $groupId, $shopId);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingAProductImageCanNotBeRemoved(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $product = $this->getProduct($productId, $groupId, $shopId, self::PRODUCT_IMAGE_PATH);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with([$productId], $groupId, $shopId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$product]));

        $input = new ProductRemoveDto($productId, $groupId, $shopId);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }
}

<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductRemoveAllGroupsProducts;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsDto;
use Product\Domain\Service\ProductRemoveAllGroupsProducts\ProductRemoveAllGroupsProductsService;

class ProductRemoveAllGroupsProductsServiceTest extends TestCase
{
    private const string PRODUCT_IMAGE_PATH = 'path/to/product/images';
    private const string GROUP_ID_1 = 'group id 1';
    private const string GROUP_ID_2 = 'group id 2';

    private ProductRemoveAllGroupsProductsService $object;
    private MockObject&ProductRepositoryInterface $productRepository;
    private MockObject&EntityImageRemoveService $entityImageRemoveService;
    /**
     * @var MockObject&PaginatorInterface<int, Product>
     */
    private MockObject&PaginatorInterface $productsPaginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->entityImageRemoveService = $this->createMock(EntityImageRemoveService::class);
        $this->productsPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ProductRemoveAllGroupsProductsService(
            $this->productRepository,
            $this->entityImageRemoveService,
            self::PRODUCT_IMAGE_PATH
        );
    }

    /**
     * @return Product[]
     */
    private function getGroupsProducts(): array
    {
        return [
            Product::fromPrimitives(
                'product 1 id',
                'group 1',
                'product 1 name',
                'product 1 description',
                null
            ),
            Product::fromPrimitives(
                'product 2 id',
                'group 1',
                'product 2 name',
                'product 2 description',
                null
            ),
            Product::fromPrimitives(
                'product 3 id',
                'group 1',
                'product 3 name',
                'product 3 description',
                null
            ),
            Product::fromPrimitives(
                'product 4 id',
                'group 2',
                'product 4 name',
                'product 4 description',
                null
            ),
        ];
    }

    /**
     * @param Product[] $products
     *
     * @return Identifier[]
     */
    private function getProductsId(array $products): array
    {
        return array_map(
            fn (Product $product): Identifier => $product->getId(),
            $products
        );
    }

    #[Test]
    public function itShouldRemoveAllProductsFromGroups(): void
    {
        $products = $this->getGroupsProducts();
        $productsId = $this->getProductsId($products);
        $productImagePath = ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH);
        $input = new ProductRemoveAllGroupsProductsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupsProductsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($products));

        $this->entityImageRemoveService
            ->expects($this->exactly(count($products)))
            ->method('__invoke')
            ->with(
                $this->callback(function (Product $product) use ($products): true {
                    $this->assertContainsEquals($product, $products);

                    return true;
                }),
                $productImagePath
            );

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products);

        $return = $this->object->__invoke($input);

        $this->assertEquals($productsId, $return);
    }

    #[Test]
    public function itShouldFailNoProductsFound(): void
    {
        $input = new ProductRemoveAllGroupsProductsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupsProductsOrFail')
            ->with($input->groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->productsPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->entityImageRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveImagenError(): void
    {
        $products = $this->getGroupsProducts();
        $productImagePath = ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH);
        $input = new ProductRemoveAllGroupsProductsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupsProductsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($products));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(function (Product $product) use ($products): true {
                    $this->assertContainsEquals($product, $products);

                    return true;
                }),
                $productImagePath
            )
            ->willThrowException(new DomainInternalErrorException());

        $this->productRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveEntitiesError(): void
    {
        $products = $this->getGroupsProducts();
        $productImagePath = ValueObjectFactory::createPath(self::PRODUCT_IMAGE_PATH);
        $input = new ProductRemoveAllGroupsProductsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupsProductsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($products));

        $this->entityImageRemoveService
            ->expects($this->exactly(count($products)))
            ->method('__invoke')
            ->with(
                $this->callback(function (Product $product) use ($products): true {
                    $this->assertContainsEquals($product, $products);

                    return true;
                }),
                $productImagePath
            );

        $this->productRepository
            ->expects($this->once())
            ->method('remove')
            ->with($products)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

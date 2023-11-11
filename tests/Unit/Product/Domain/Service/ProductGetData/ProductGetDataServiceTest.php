<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetData\Dto\ProductGetDataDto;
use Product\Domain\Service\ProductGetData\ProductGetDataService;

class ProductGetDataServiceTest extends TestCase
{
    private ProductGetDataService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ProductGetDataService($this->productRepository);
    }

    private function getProducts(): array
    {
        return [
            Product::fromPrimitives('product 1 id', 'group id', 'product 1 name', 'product 1 description', null),
            Product::fromPrimitives('product 2 id', 'group id', 'product 2 name', 'product 2 description', null),
            Product::fromPrimitives('product 3 id', 'group id', 'product 3 name', 'product 3 description', null),
        ];
    }

    private function assertProductDataIsOk(array $productsDataExpected, array $productDataActual): void
    {
        $this->assertArrayHasKey('id', $productDataActual);
        $this->assertArrayHasKey('group_id', $productDataActual);
        $this->assertArrayHasKey('name', $productDataActual);
        $this->assertArrayHasKey('description', $productDataActual);
        $this->assertArrayHasKey('image', $productDataActual);
        $this->assertArrayHasKey('created_on', $productDataActual);

        $this->assertContainsEquals(
            $productDataActual['id'],
            array_map(
                fn (Product $product) => $product->getId()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['group_id'],
            array_map(
                fn (Product $product) => $product->getGroupId()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['name'],
            array_map(
                fn (Product $product) => $product->getName()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['description'],
            array_map(
                fn (Product $product) => $product->getDescription()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['image'], array_map(
                fn (Product $product) => $product->getImage()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertIsString($productDataActual['created_on']);
    }

    /** @test */
    public function itShouldGetProductsData(): void
    {
        $products = $this->getProducts();
        $productNameStartsWith = null;
        $productsMaxNumber = 100;
        $productName = ValueObjectFactory::createNameWithSpaces('product name');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [
            ValueObjectFactory::createIdentifier('product 1 id'),
        ];
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $input = new ProductGetDataDto($groupId, $productsId, $shopsId, $productNameStartsWith, $productName);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($productsId, $groupId, $shopsId, $productName, $productNameStartsWith)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, $productsMaxNumber);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($products, $product);
        }
    }

    /** @test */
    public function itShouldGetProductsDataAllInputsAreNull(): void
    {
        $productNameStartsWith = null;
        $productName = ValueObjectFactory::createNameWithSpaces(null);
        $groupId = ValueObjectFactory::createIdentifier(null);
        $productsId = [];
        $shopsId = [];
        $input = new ProductGetDataDto($groupId, $productsId, $shopsId, $productNameStartsWith, $productName);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with(null, null, null, $productName, null)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGetProductsDataNoProductsFound(): void
    {
        $productNameStartsWith = null;
        $productName = ValueObjectFactory::createNameWithSpaces(null);
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productsId = [
            ValueObjectFactory::createIdentifier('product 1 id'),
        ];
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $input = new ProductGetDataDto($groupId, $productsId, $shopsId, $productNameStartsWith, $productName);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($productsId, $groupId, $shopsId, $productName, $productNameStartsWith)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}

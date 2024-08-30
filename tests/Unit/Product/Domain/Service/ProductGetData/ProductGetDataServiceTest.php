<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductGetData;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetData\Dto\ProductGetDataDto;
use Product\Domain\Service\ProductGetData\ProductGetDataService;

class ProductGetDataServiceTest extends TestCase
{
    private const string APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const string PRODUCT_PUBLIC_PATH = '/group/public/path';

    private ProductGetDataService $object;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ProductGetDataService($this->productRepository, self::PRODUCT_PUBLIC_PATH, self::APP_PROTOCOL_AND_DOMAIN);
    }

    private function getProducts(): array
    {
        return [
            Product::fromPrimitives('product 1 id', 'group id', 'product 1 name', 'product 1 description', 'imageName.jpg'),
            Product::fromPrimitives('product 2 id', 'group id', 'product 2 name', 'product 2 description', null),
            Product::fromPrimitives('product 3 id', 'group id', 'product 3 name', 'product 3 description', null),
        ];
    }

    private function getProductsExpected(): array
    {
        $products = $this->getProducts();

        return array_map(
            function (Product $product): Product {
                if (!$product->getImage()->isNull()) {
                    $product->setImage(
                        ValueObjectFactory::createPath(self::APP_PROTOCOL_AND_DOMAIN.self::PRODUCT_PUBLIC_PATH.'/'.$product->getImage()->getValue())
                    );
                }

                return $product;
            },
            $products
        );
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
                fn (Product $product): ?string => $product->getId()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['group_id'],
            array_map(
                fn (Product $product): ?string => $product->getGroupId()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['name'],
            array_map(
                fn (Product $product): ?string => $product->getName()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['description'],
            array_map(
                fn (Product $product): ?string => $product->getDescription()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual['image'], array_map(
                fn (Product $product): ?string => $product->getImage()->getValue(),
                $productsDataExpected
            )
        );
        $this->assertIsString($productDataActual['created_on']);
    }

    #[Test]
    public function itShouldGetProductOfAGroupOrderAsc(): void
    {
        $products = $this->getProducts();
        $productsExpected = $this->getProductsExpected();
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            ValueObjectFactory::createNameWithSpaces(null),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, null, null, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameFilterOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($productsExpected, $product);
        }
    }

    #[Test]
    public function itShouldGetProductOfAGroupWithProductsIdAndShopsId(): void
    {
        $products = $this->getProducts();
        $productsExpected = $this->getProductsExpected();
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [ValueObjectFactory::createIdentifier('product 1 id')],
            [ValueObjectFactory::createIdentifier('shop 1 id')],
            ValueObjectFactory::createNameWithSpaces(null),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, $input->productsId, $input->shopsId, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameFilterOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($productsExpected, $product);
        }
    }

    #[Test]
    public function itShouldGetProductOfAGroupWithProductName(): void
    {
        $products = $this->getProducts();
        $productsExpected = $this->getProductsExpected();
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            ValueObjectFactory::createNameWithSpaces('product name'),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByProductNameOrFail')
            ->with($input->groupId, $input->productName, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameFilterOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($productsExpected, $product);
        }
    }

    #[Test]
    public function itShouldGetProductOfAGroupWithProductNameFilter(): void
    {
        $products = $this->getProducts();
        $productsExpected = $this->getProductsExpected();
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            ValueObjectFactory::createNameWithSpaces(null),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces('product name filter')
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameOrFail');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByProductNameFilterOrFail')
            ->with($input->groupId, $input->productNameFilter, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($productsExpected, $product);
        }
    }

    #[Test]
    public function itShouldGetProductOfAGroupWithShopNameFilter(): void
    {
        $products = $this->getProducts();
        $productsExpected = $this->getProductsExpected();
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            ValueObjectFactory::createNameWithSpaces(null),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces('shop name filter')
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameFilterOrFail');

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsByShopNameFilterOrFail')
            ->with($input->groupId, $input->shopNameFilter, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($products));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($products), $return);

        foreach ($return as $product) {
            $this->assertProductDataIsOk($productsExpected, $product);
        }
    }

    #[Test]
    public function itShouldFailGettingProductOfAGroupNotEnoughParameters(): void
    {
        $input = new ProductGetDataDto(
            ValueObjectFactory::createIdentifier(null),
            [],
            [],
            ValueObjectFactory::createNameWithSpaces(null),
            new Filter(
                'product_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            true,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByProductNameFilterOrFail');

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(LogicException::class);
        $this->object->__invoke($input);
    }
}

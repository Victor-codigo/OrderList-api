<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductGetShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductGetShopPrice\Dto\ProductGetShopPriceDto;
use Product\Domain\Service\ProductGetShopPrice\ProductGetShopPriceService;
use Shop\Domain\Model\Shop;

class ProductGetShopPriceServiceTest extends TestCase
{
    private ProductGetShopPriceService $object;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ProductGetShopPriceService($this->productShopRepository);
    }

    private function getProductsPrice(): array
    {
        /** @var MockObject|Product $product */
        $product = $this
            ->getMockBuilder(Product::class)
            ->setConstructorArgs([
                ValueObjectFactory::createIdentifier('product id'),
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createNameWithSpaces('product name'),
                ValueObjectFactory::createDescription('product description'),
                ValueObjectFactory::createPath('product image path'),
            ])
            ->onlyMethods([])
            ->getMock();

        /** @var MockObject|Shop $shop */
        $shop = $this
            ->getMockBuilder(Shop::class)
            ->setConstructorArgs([
                ValueObjectFactory::createIdentifier('shop id'),
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createNameWithSpaces('shop name'),
                ValueObjectFactory::createDescription('shop description'),
                ValueObjectFactory::createPath('shop image path'),
            ])
            ->onlyMethods([])
            ->getMock();

        return [
            ProductShop::fromPrimitives(
                $product,
                $shop,
                14.2
            ),
            ProductShop::fromPrimitives(
                $product,
                $shop,
                16.8
            ),
            ProductShop::fromPrimitives(
                $product,
                $shop,
                0
            ),
            ProductShop::fromPrimitives(
                $product,
                $shop,
                null
            ),
        ];
    }

    private function assertProductShopIsOk(ProductShop $productExpected, array $productActual): void
    {
        $this->assertArrayHasKey('product_id', $productActual);
        $this->assertArrayHasKey('shop_id', $productActual);
        $this->assertArrayHasKey('price', $productActual);
        $this->assertEquals($productExpected->getProductId()->getValue(), $productActual['product_id']);
        $this->assertEquals($productExpected->getShopId()->getValue(), $productActual['shop_id']);
        $this->assertEquals($productExpected->getPrice()->getValue(), $productActual['price']);
    }

    /** @test */
    public function itShouldGetProductsPrices(): void
    {
        $productsExpected = $this->getProductsPrice();
        $input = new ProductGetShopPriceDto(
            [ValueObjectFactory::createIdentifier('product id')],
            [ValueObjectFactory::createIdentifier('shop id')],
            ValueObjectFactory::createIdentifier('group id')
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with($input->productsId, $input->shopsId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$productsExpected[0]]));

        $return = $this->object->__invoke($input);

        $this->assertCount(1, $return);
        $this->assertProductShopIsOk($productsExpected[0], $return[0]);
    }

    /** @test */
    public function itShouldFailGetProductsPricesProductNotFound(): void
    {
        $input = new ProductGetShopPriceDto(
            [ValueObjectFactory::createIdentifier('product id')],
            [ValueObjectFactory::createIdentifier('shop id')],
            ValueObjectFactory::createIdentifier('group id')
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with($input->productsId, $input->shopsId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}

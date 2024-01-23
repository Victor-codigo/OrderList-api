<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductShop;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductShop\Dto\ProductShopDto;
use Product\Domain\Service\ProductShop\ProductShopService;
use Shop\Domain\Model\Shop;

class ProductShopServiceTest extends TestCase
{
    private ProductShopService $object;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|ProductShop $productShop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->productShop = $this->createMock(ProductShop::class);
        $this->object = new ProductShopService($this->productShopRepository);
    }

    private function getProduct(): Product
    {
        return Product::fromPrimitives(
            'product id',
            'group id',
            'product name',
            'product description',
            null
        );
    }

    private function getShop(): Shop
    {
        return Shop::fromPrimitives(
            'shop id',
            'group id',
            'shop name',
            'shop description',
            null
        );
    }

    /** @test */
    public function itShouldRemoveAProductShopNotExists(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(50),
            true
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $return = $this->object->__invoke($input);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldRemoveAProductShopAlreadyExists(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(50),
            true
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$this->productShop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $return = $this->object->__invoke($input);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldFailRemoveAProductShopAlreadyExistsCanNotRemove(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(50),
            true
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$this->productShop])
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldCreateAProductShop(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(50),
            false
        );
        $productShopCreatedExpected = new ProductShop($input->product, $input->shop, $input->price);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([$productShopCreatedExpected]);

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $return = $this->object->__invoke($input);

        $this->assertEquals($productShopCreatedExpected, $return);
    }

    /** @test */
    public function itShouldCreateAProductShopCanNotCreate(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(50),
            false
        );
        $productShopCreatedExpected = new ProductShop($input->product, $input->shop, $input->price);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([$productShopCreatedExpected])
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldUpdateAProductShop(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(100),
            false,
        );
        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([$this->productShop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productShop
            ->expects($this->once())
            ->method('setPrice')
            ->with($input->price);

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(ProductShop::class, $return);
    }

    /** @test */
    public function itShouldUpdateAProductShopNoChanges(): void
    {
        $input = new ProductShopDto(
            $this->getProduct(),
            $this->getShop(),
            ValueObjectFactory::createMoney(null),
            false,
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$input->product->getId()], [$input->shop->getId()], $input->product->getGroupId())
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([$this->productShop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->productShop
            ->expects($this->never())
            ->method('setPrice');

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(ProductShop::class, $return);
    }
}

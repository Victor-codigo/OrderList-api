<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductSetShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductSetShopPrice\Dto\ProductSetShopPriceDto;
use Product\Domain\Service\ProductSetShopPrice\ProductSetShopPriceService;

class ProductSetShopPriceServiceTest extends TestCase
{
    private ProductSetShopPriceService $object;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|ProductShop $productShop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->productShop = $this->createMock(ProductShop::class);
        $this->object = new ProductSetShopPriceService($this->productShopRepository);
    }

    /** @test */
    public function itShouldSetTheProductPriceForAShop(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $price = ValueObjectFactory::createMoney(15.6);
        $input = new ProductSetShopPriceDto($productId, $shopId, $groupId, $price);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productId], [$shopId], $groupId)
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productShop);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->productShop
            ->expects($this->once())
            ->method('setPrice')
            ->with($price);

        $return = $this->object->__invoke($input);

        $this->assertEquals($this->productShop, $return);
    }

    /** @test */
    public function itShouldFailSettingTheProductPriceForAShopProductOrShopNotFound(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $price = ValueObjectFactory::createMoney(15.6);
        $input = new ProductSetShopPriceDto($productId, $shopId, $groupId, $price);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productId], [$shopId], $groupId)
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShop
            ->expects($this->never())
            ->method('setPrice');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailSettingTheProductPriceForAShopErrorDatabase(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $price = ValueObjectFactory::createMoney(15.6);
        $input = new ProductSetShopPriceDto($productId, $shopId, $groupId, $price);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productId], [$shopId], $groupId)
            ->willReturn($this->paginator);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->productShop)
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productShop]));

        $this->productShop
            ->expects($this->once())
            ->method('setPrice')
            ->with($price);

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

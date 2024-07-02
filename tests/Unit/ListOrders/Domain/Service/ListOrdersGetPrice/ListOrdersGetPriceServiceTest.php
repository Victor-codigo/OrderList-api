<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersGetPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceDto;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceOutputDto;
use ListOrders\Domain\Service\ListOrdersGetPrice\ListOrdersGetPriceService;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Shop\Domain\Model\Shop;

class ListOrdersGetPriceServiceTest extends TestCase
{
    private const int LIST_ORDERS_MAX_ORDERS = 500;
    private const int PRICE_TOTAL = 140;
    private const int PRICE_BOUGHT = 100;

    private ListOrdersGetPriceService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|PaginatorInterface $ordersPagination;
    private MockObject|PaginatorInterface $productShopsPagination;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->ordersPagination = $this->createMock(PaginatorInterface::class);
        $this->productShopsPagination = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersGetPriceService(
            $this->orderRepository,
            $this->productShopRepository
        );
    }

    /**
     * @return Product[]
     */
    private function getProducts(): array
    {
        return [
            Product::fromPrimitives(
                'product 1 id',
                'group id',
                'product name 1',
                'product description 1',
                null,
            ),
            Product::fromPrimitives(
                'product 2 id',
                'group id',
                'product name 2',
                'product description 2',
                null,
            ),
            Product::fromPrimitives(
                'product 3 id',
                'group id',
                'product name 3',
                'product description 3',
                null,
            ),
            Product::fromPrimitives(
                'product 4 id',
                'group id',
                'product name 4',
                'product description 4',
                null,
            ),
        ];
    }

    /**
     * @return Shop[]
     */
    private function getShops(): array
    {
        return [
            Shop::fromPrimitives(
                'shop 1 id',
                'group id',
                'shop 1 name',
                'shop 1 description',
                null,
            ),
            Shop::fromPrimitives(
                'shop 2 id',
                'group id',
                'shop 2 name',
                'shop 2 description',
                null,
            ),
            Shop::fromPrimitives(
                'shop 3 id',
                'group id',
                'shop 3 name',
                'shop 3 description',
                null,
            ),
            Shop::fromPrimitives(
                'shop 4 id',
                'group id',
                'shop 4 name',
                'shop 4 description',
                null,
            ),
        ];
    }

    /**
     * @return Order[]
     */
    private function getOrders(): array
    {
        $listOrders = $this->createMock(ListOrders::class);
        $products = $this->getProducts();
        $shops = $this->getShops();

        return [
            Order::fromPrimitives(
                'order 1 id',
                'group id',
                'user 1 id',
                'order 1 description',
                1,
                true,
                $listOrders,
                $products[0],
                $shops[0]
            ),
            Order::fromPrimitives(
                'order 2 id',
                'group id',
                'user 2 id',
                'order 2 description',
                2,
                false,
                $listOrders,
                $products[1],
                $shops[1]
            ),
            Order::fromPrimitives(
                'order 3 id',
                'group id',
                'user 3 id',
                'order 3 description',
                3,
                true,
                $listOrders,
                $products[2],
                $shops[2]
            ),
            Order::fromPrimitives(
                'order 4 id',
                'group 4 id',
                'user 4 id',
                'order 4 description',
                4,
                true,
                $listOrders,
                $products[3],
                $shops[3]
            ),
            Order::fromPrimitives(
                'order 5 id',
                'group 5 id',
                'user 5 id',
                'order 5 description',
                4,
                true,
                $listOrders,
                $products[2],
                $shops[3]
            ),
        ];
    }

    /**
     * @return ProductShop[]
     */
    private function getProductShops(): array
    {
        $orders = $this->getOrders();

        return [
            ProductShop::fromPrimitives(
                $orders[0]->getProduct(),
                $orders[0]->getShop(),
                10,
                UNIT_MEASURE_TYPE::KG
            ),
            ProductShop::fromPrimitives(
                $orders[1]->getProduct(),
                $orders[1]->getShop(),
                20,
                UNIT_MEASURE_TYPE::M
            ),
            ProductShop::fromPrimitives(
                $orders[2]->getProduct(),
                $orders[2]->getShop(),
                30,
                UNIT_MEASURE_TYPE::L
            ),
            ProductShop::fromPrimitives(
                $orders[3]->getProduct(),
                $orders[3]->getShop(),
                null,
                null
            ),
        ];
    }

    /**
     * @param Order[] $orders
     *
     * @return Identifier[]
     */
    private function getOrdersProductsId(array $orders): array
    {
        return array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
    }

    /**
     * @param Order[] $orders
     *
     * @return Identifier[]
     */
    private function getOrdersShopsId(array $orders): array
    {
        return array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
    }

    /** @test */
    public function itShouldGetTotalForAListOfOrders(): void
    {
        $orders = $this->getOrders();
        $productsShops = $this->getProductShops();
        $productsId = $this->getOrdersProductsId($orders);
        $shopsId = $this->getOrdersShopsId($orders);
        $input = new ListOrdersGetPriceDto(
            ValueObjectFactory::createIdentifier('list orders id'),
            ValueObjectFactory::createIdentifier('group id'),
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdOrFail')
            ->with($input->listOrdersId, $input->groupId)
            ->willReturn($this->ordersPagination);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with($productsId, $shopsId, $input->groupId)
            ->willReturn($this->productShopsPagination);

        $this->ordersPagination
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, self::LIST_ORDERS_MAX_ORDERS);

        $this->ordersPagination
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($orders));

        $this->productShopsPagination
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsShops));

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(ListOrdersGetPriceOutputDto::class, $return);
        $this->assertEquals(
            new ListOrdersGetPriceOutputDto(
                ValueObjectFactory::createMoney(self::PRICE_TOTAL),
                ValueObjectFactory::createMoney(self::PRICE_BOUGHT)
            ),
            $return
        );
    }

    /** @test */
    public function itShouldFailGetPriceListOrderNotFound(): void
    {
        $input = new ListOrdersGetPriceDto(
            ValueObjectFactory::createIdentifier('list orders id not found'),
            ValueObjectFactory::createIdentifier('group id'),
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdOrFail')
            ->with($input->listOrdersId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepository
            ->expects($this->never())
            ->method('findProductsAndShopsOrFail');

        $this->ordersPagination
            ->expects($this->never())
            ->method('setPagination');

        $this->ordersPagination
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopsPagination
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldGetPriceProductsHasNotShop(): void
    {
        $orders = $this->getOrders();
        $input = new ListOrdersGetPriceDto(
            ValueObjectFactory::createIdentifier('list orders id not found'),
            ValueObjectFactory::createIdentifier('group id'),
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdOrFail')
            ->with($input->listOrdersId, $input->groupId)
            ->willReturn($this->ordersPagination);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->willThrowException(new DBNotFoundException());

        $this->ordersPagination
            ->expects($this->once())
            ->method('setPagination');

        $this->ordersPagination
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($orders));

        $this->productShopsPagination
            ->expects($this->never())
            ->method('getIterator');

        $return = $this->object->__invoke($input);

        $this->assertEquals(
            new ListOrdersGetPriceOutputDto(
                ValueObjectFactory::createMoney(0),
                ValueObjectFactory::createMoney(0)
            ),
            $return
        );
    }
}

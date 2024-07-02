<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderCreate\Dto\OrderCreateDto;
use Order\Domain\Service\OrderCreate\Dto\OrderDataServiceDto;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateListOrdersNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductShopRepeatedException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateShopNotFoundException;
use Order\Domain\Service\OrderCreate\OrderCreateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderCreateServiceTest extends TestCase
{
    public const LIST_ORDERS_ID = 'e39d9a07-0f40-42a7-84da-c90cdbd77bec';

    private OrderCreateService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|PaginatorInterface $paginator;
    private Identifier $groupId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderCreateService(
            $this->orderRepository,
            $this->listOrdersRepository,
            $this->productRepository,
            $this->shopRepository
        );

        $this->groupId = ValueObjectFactory::createIdentifier('group id');
    }

    /**
     * @return ListOrders[]
     */
    private function getListsOrders(): array
    {
        $ordersData = $this->getOrdersData();

        return [
            ListOrders::fromPrimitives(
                self::LIST_ORDERS_ID,
                'group id',
                $ordersData[0]->userId->getValue(),
                'list order 1 name',
                'list order 1 description',
                null
            ),
            ListOrders::fromPrimitives(
                self::LIST_ORDERS_ID,
                'group id',
                $ordersData[1]->userId->getValue(),
                'list order 2 name',
                'list order 2 description',
                null
            ),
        ];
    }

    /**
     * @return Product[]
     */
    private function getProducts(): array
    {
        $ordersData = $this->getOrdersData();

        return [
            Product::fromPrimitives(
                $ordersData[0]->productId->getValue(),
                'product group id 1',
                'product name 1',
                'product description 1',
                null
            ),
            Product::fromPrimitives(
                $ordersData[1]->productId->getValue(),
                'product group id 2',
                'product name 2',
                'product description 2',
                null
            ),
            Product::fromPrimitives(
                $ordersData[2]->productId->getValue(),
                'product group id 3',
                'product name 3',
                'product description 3',
                null
            ),
        ];
    }

    /**
     * @return Shop[]
     */
    private function getShops(): array
    {
        $ordersData = $this->getOrdersData();

        return [
            Shop::fromPrimitives(
                $ordersData[0]->shopId->getValue(),
                'shop group id 1',
                'shop name 1',
                'shop description 1',
                null
            ),
            Shop::fromPrimitives(
                $ordersData[1]->shopId->getValue(),
                'shop group id 2',
                'shop name 2',
                'shop description 2',
                null
            ),
            Shop::fromPrimitives(
                $ordersData[2]->shopId->getValue(),
                'shop group id 3',
                'shop name 3',
                'shop description 3',
                null
            ),
        ];
    }

    /**
     * @return OrderDataServiceDto[]
     */
    private function getOrdersData(bool $shopIsIsNull = false): array
    {
        return [
            new OrderDataServiceDto(
                ValueObjectFactory::createIdentifier('product id 1'),
                ValueObjectFactory::createIdentifier('user id 1'),
                ValueObjectFactory::createIdentifierNullable(!$shopIsIsNull ? 'shop id 1' : null),
                ValueObjectFactory::createDescription('order 1 description'),
                ValueObjectFactory::createAmount(15),
            ),

            new OrderDataServiceDto(
                ValueObjectFactory::createIdentifier('product id 2'),
                ValueObjectFactory::createIdentifier('user id 2'),
                ValueObjectFactory::createIdentifierNullable(!$shopIsIsNull ? 'shop id 2' : null),
                ValueObjectFactory::createDescription('order 2 description'),
                ValueObjectFactory::createAmount(34),
            ),

            new OrderDataServiceDto(
                ValueObjectFactory::createIdentifier('product id 3'),
                ValueObjectFactory::createIdentifier('user id 3'),
                ValueObjectFactory::createIdentifierNullable(!$shopIsIsNull ? 'shop id 3' : null),
                ValueObjectFactory::createDescription('order 3 description'),
                ValueObjectFactory::createAmount(26),
            ),
        ];
    }

    /**
     * @return Order[]
     */
    private function getOrders(bool $shopIsIsNull = false): array
    {
        $listOrders = $this->getListsOrders();
        $orders = $this->getOrdersData($shopIsIsNull);
        $products = $this->getProducts();

        $shops = null;
        if (!$shopIsIsNull) {
            $shops = $this->getShops();
        }

        return [
            new Order(
                ValueObjectFactory::createIdentifier('order id 1'),
                ValueObjectFactory::createIdentifier('group id'),
                $orders[0]->userId,
                $orders[0]->description,
                $orders[0]->amount,
                false,
                $listOrders[0],
                $products[0],
                $shops[0] ?? null
            ),
            new Order(
                ValueObjectFactory::createIdentifier('order id 2'),
                ValueObjectFactory::createIdentifier('group id'),
                $orders[1]->userId,
                $orders[1]->description,
                $orders[1]->amount,
                true,
                $listOrders[1],
                $products[1],
                $shops[1] ?? null
            ),
            new Order(
                ValueObjectFactory::createIdentifier('order id 3'),
                ValueObjectFactory::createIdentifier('group id'),
                $orders[2]->userId,
                $orders[2]->description,
                $orders[2]->amount,
                false,
                $listOrders[1],
                $products[2],
                $shops[2] ?? null
            ),
        ];
    }

    private function assertOrderIsEqual(Order $expected, Order $actual): void
    {
        $this->assertEquals($expected->getId(), $actual->getId());
        $this->assertEquals($expected->getProductId(), $actual->getProductId());
        $this->assertEquals($expected->getShopId(), $actual->getShopId());
        $this->assertEquals($expected->getUserId(), $actual->getUserId());
        $this->assertEquals($expected->getGroupId(), $actual->getGroupId());
        $this->assertEquals($expected->getDescription(), $actual->getDescription());
        $this->assertEquals($expected->getAmount(), $actual->getAmount());
        $this->assertInstanceOf(\DateTime::class, $actual->getCreatedOn());
    }

    /** @test */
    public function itShouldCreateOrders(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $shops = $this->getShops();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($this->groupId, $shopsId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail')
            ->with($this->groupId, $listOrdersId, $productsId, $shopsId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection($listsOrders),
                new ArrayCollection($products),
                new ArrayCollection($shops),
            );

        $this->paginator
            ->expects($this->exactly(3))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->exactly(count($ordersData)))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(
                $orders[0]->getId()->getValue(),
                $orders[1]->getId()->getValue(),
                $orders[2]->getId()->getValue()
            );

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $ordersToSave) use ($orders) {
                /** @var Order[] $ordersToSave */
                foreach ($ordersToSave as $key => $orderToSave) {
                    $this->assertOrderIsEqual($orders[$key], $orderToSave);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        foreach ($orders as $key => $order) {
            $this->assertOrderIsEqual($order, $return[$key]);
        }
    }

    /** @test */
    public function itShouldCreateOrdersShopsIdAreNull(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders(true);
        $ordersData = $this->getOrdersData(true);
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($this->groupId, $shopsId)
            ->willThrowException(new DBNotFoundException());

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail')
            ->with($this->groupId, $listOrdersId, $productsId, [])
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection($listsOrders),
                new ArrayCollection($products),
            );

        $this->paginator
            ->expects($this->exactly(2))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->exactly(count($ordersData)))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(
                $orders[0]->getId()->getValue(),
                $orders[1]->getId()->getValue(),
                $orders[2]->getId()->getValue()
            );

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $ordersToSave) use ($orders) {
                /** @var Order[] $ordersToSave */
                foreach ($ordersToSave as $key => $orderToSave) {
                    $this->assertOrderIsEqual($orders[$key], $orderToSave);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        foreach ($orders as $key => $order) {
            $this->assertOrderIsEqual($order, $return[$key]);
        }
    }

    /** @test */
    public function itShouldFailCreatingOrdersListsOrdersNotFound(): void
    {
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $ordersData = $this->getOrdersData();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('generateId');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail');

        $this->paginator
                ->expects($this->never())
                ->method('getIterator');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->expectException(OrderCreateListOrdersNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingOrdersProductsNotFound(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('generateId');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator($listsOrders)
            );

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1);

        $this->expectException(OrderCreateProductNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingOrdersNotFoundAllProducts(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail');

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator($listsOrders),
                new ArrayCollection([$products[0], $products[1]])
            );

        $this->paginator
            ->expects($this->exactly(2))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->never())
            ->method('generateId');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(OrderCreateProductNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingOrdersNotFoundAllShops(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $shops = $this->getShops();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($this->groupId, $shopsId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail');

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator($listsOrders),
                new ArrayCollection($products),
                new ArrayCollection([$shops[0]])
            );

        $this->paginator
            ->expects($this->exactly(3))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->never())
            ->method('generateId');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(OrderCreateShopNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingOrdersProductOrShopAlreadyInListOrders(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $shops = $this->getShops();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($this->groupId, $shopsId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail')
            ->with($this->groupId, $listOrdersId, $productsId, $shopsId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection($listsOrders),
                new ArrayCollection($products),
                new ArrayCollection($shops),
            );

        $this->paginator
            ->expects($this->exactly(3))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->never())
            ->method('generateId');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(OrderCreateProductShopRepeatedException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingOrdersDatabaseException(): void
    {
        $listsOrders = $this->getListsOrders();
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $orders = $this->getOrders();
        $ordersData = $this->getOrdersData();
        $productsId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $products = $this->getProducts();
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $shops = $this->getShops();
        $input = new OrderCreateDto($this->groupId, $listOrdersId, $ordersData);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$listOrdersId], $this->groupId)
            ->willReturn($this->paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($this->groupId, $productsId)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($this->groupId, $shopsId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdProductIdAndShopIdOrFail')
            ->with($this->groupId, $listOrdersId, $productsId, $shopsId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection($listsOrders),
                new ArrayCollection($products),
                new ArrayCollection($shops)
            );

        $this->paginator
            ->expects($this->exactly(3))
            ->method('setPagination')
            ->with(1);

        $this->orderRepository
            ->expects($this->exactly(count($ordersData)))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(
                $orders[0]->getId()->getValue(),
                $orders[1]->getId()->getValue(),
                $orders[2]->getId()->getValue()
            );

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $ordersToSave) use ($orders) {
                /** @var Order[] $ordersToSave */
                foreach ($ordersToSave as $key => $orderToSave) {
                    $this->assertOrderIsEqual($orders[$key], $orderToSave);
                }

                return true;
            }))
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }
}

<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Order\Domain\Service\OrderGetData\OrderGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Test\Unit\DataBaseTestCase;

class OrderGetDataServiceTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
    ];
    private OrderGetDataService $object;
    private MockObject|OrderRepositoryInterface $orderRepositoryMock;
    private OrderRepositoryInterface $orderRepository;
    private ProductShopRepositoryInterface $productShopRepository;
    private MockObject|ProductShopRepositoryInterface $productShopRepositoryMock;
    private ProductRepositoryInterface $productRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->productShopRepositoryMock = $this->createMock(ProductShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderGetDataService($this->orderRepositoryMock, $this->productShopRepositoryMock);

        $this->productShopRepository = $this->entityManager->getRepository(ProductShop::class);
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->orderRepository = $this->entityManager->getRepository(Order::class);
    }

    /**
     * @return Identifier[]
     */
    private function getOrdersIdentifiers(): array
    {
        return array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            self::ORDERS_ID
        );
    }

    /**
     * @return Order[]
     */
    private function getOrders(): array
    {
        $orders = $this->orderRepository->findBy(['id' => self::ORDERS_ID]);

        return array_combine(
            array_map(
                fn (Order $order) => $order->getProductId()->getValue(),
                $orders
            ),
            $orders
        );
    }

    /**
     * @param Order[] $orders
     */
    private function getProductsShops(array $orders): array
    {
        $shopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );

        $productsShopsExpected = $this->productShopRepository->findBy([
            'productId' => array_keys($orders),
            'shopId' => $shopsId,
        ]);

        return array_combine(
            array_map(
                fn (ProductShop $productShop) => $productShop->getProductId()->getValue(),
                $productsShopsExpected
            ),
            $productsShopsExpected
        );
    }

    /**
     * @param Order[] $orders
     *
     * @return Identifier[]
     */
    private function getShopsId(array $orders): array
    {
        return array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
    }

    private function assertOrderDataIsOk(Order $orderExpected, Money $price, array $orderDataActual): void
    {
        $this->assertArrayHasKey('id', $orderDataActual);
        $this->assertArrayHasKey('product_id', $orderDataActual);
        $this->assertArrayHasKey('shop_id', $orderDataActual);
        $this->assertArrayHasKey('user_id', $orderDataActual);
        $this->assertArrayHasKey('group_id', $orderDataActual);
        $this->assertArrayHasKey('description', $orderDataActual);
        $this->assertArrayHasKey('amount', $orderDataActual);
        $this->assertArrayHasKey('created_on', $orderDataActual);
        $this->assertArrayHasKey('price', $orderDataActual);

        $this->assertEquals($orderExpected->getId()->getValue(), $orderDataActual['id']);
        $this->assertEquals($orderExpected->getProductId()->getValue(), $orderDataActual['product_id']);
        $this->assertEquals($orderExpected->getShopId()->getValue(), $orderDataActual['shop_id']);
        $this->assertEquals($orderExpected->getUserId()->getValue(), $orderDataActual['user_id']);
        $this->assertEquals($orderExpected->getGroupId()->getValue(), $orderDataActual['group_id']);
        $this->assertEquals($orderExpected->getDescription()->getValue(), $orderDataActual['description']);
        $this->assertEquals($orderExpected->getAmount()->getValue(), $orderDataActual['amount']);
        $this->assertIsString($orderDataActual['created_on']);
        $this->assertEquals($price->getValue(), $orderDataActual['price']);
    }

    /** @test */
    public function itShouldGetOrdersData(): void
    {
        $input = new OrderGetDataDto(
            $this->getOrdersIdentifiers(),
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );
        $ordersExpectedIndexProduct = $this->getOrders();
        $ordersExpected = array_values($ordersExpectedIndexProduct);
        $shopsId = $this->getShopsId($ordersExpected);
        $productsShopsExpected = $this->getProductsShops($ordersExpectedIndexProduct);

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->ordersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->productShopRepositoryMock
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with(array_keys($ordersExpectedIndexProduct), $shopsId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator($ordersExpected),
                new \ArrayIterator($productsShopsExpected)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(4, $return);

        foreach ($return as $key => $orderData) {
            $this->assertOrderDataIsOk(
                $ordersExpected[$key],
                $productsShopsExpected[$ordersExpected[$key]->getProductId()->getValue()]->getPrice(),
                $orderData
            );
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersDataProductsNotFound(): void
    {
        $input = new OrderGetDataDto(
            $this->getOrdersIdentifiers(),
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->ordersId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->productShopRepositoryMock
            ->expects($this->never())
            ->method('findProductsAndShopsOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGettingOrdersDataProductShopNotFound(): void
    {
        $input = new OrderGetDataDto(
            $this->getOrdersIdentifiers(),
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );
        $ordersExpectedIndexProduct = $this->getOrders();
        $ordersExpected = array_values($ordersExpectedIndexProduct);
        $shopsId = $this->getShopsId($ordersExpected);

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->ordersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->productShopRepositoryMock
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with(array_keys($ordersExpectedIndexProduct), $shopsId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersExpected));

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}

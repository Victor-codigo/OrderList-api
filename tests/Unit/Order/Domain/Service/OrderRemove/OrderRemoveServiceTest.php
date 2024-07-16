<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderRemove\Dto\OrderRemoveDto;
use Order\Domain\Service\OrderRemove\OrderRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;

class OrderRemoveServiceTest extends TestCase
{
    private const string ORDER_1_ID = '9a48ac5b-4571-43fd-ac80-28b08124ffb8';
    private const string ORDER_2_ID = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private OrderRemoveService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderRemoveService($this->orderRepository);
    }

    private function createOrder(array $ordersId): array
    {
        $listOrders = $this->createMock(ListOrders::class);
        $product = $this->createMock(Product::class);

        return array_map(
            fn (Identifier $orderId): Order => Order::fromPrimitives(
                $orderId->getValue(),
                'group id',
                'user id',
                'order description',
                10,
                false,
                $listOrders,
                $product
            ),

            $ordersId
        );
    }

    /** @test */
    public function itShouldRemoveSomeOrders(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            ValueObjectFactory::createIdentifier(self::ORDER_1_ID),
            ValueObjectFactory::createIdentifier(self::ORDER_2_ID),
        ];
        $orders = $this->createOrder($ordersId);

        $input = new OrderRemoveDto($groupId, $ordersId);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($groupId, $ordersId, true)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($orders);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($orders));

        $return = $this->object->__invoke($input);

        foreach ($return as $orderRemoved) {
            $this->assertContainsEquals($orderRemoved->getId()->getValue(), $ordersId);
        }
    }

    /** @test */
    public function itShouldFailRemovingSomeOrders(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            ValueObjectFactory::createIdentifier(self::ORDER_1_ID),
            ValueObjectFactory::createIdentifier(self::ORDER_2_ID),
        ];

        $input = new OrderRemoveDto($groupId, $ordersId);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($groupId, $ordersId, true)
            ->willThrowException(new DBNotFoundException());

        $this->orderRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingSomeOrdersDatabaseError(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            ValueObjectFactory::createIdentifier(self::ORDER_1_ID),
            ValueObjectFactory::createIdentifier(self::ORDER_2_ID),
        ];

        $input = new OrderRemoveDto($groupId, $ordersId);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($groupId, $ordersId, true)
            ->willThrowException(new DBConnectionException());

        $this->orderRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

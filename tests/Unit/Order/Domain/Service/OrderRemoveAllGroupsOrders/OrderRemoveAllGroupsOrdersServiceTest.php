<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderRemoveAllGroupsOrders;

use Override;
use ArrayIterator;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersDto;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\OrderRemoveAllGroupsOrdersService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;

class OrderRemoveAllGroupsOrdersServiceTest extends TestCase
{
    private OrderRemoveAllGroupsOrdersService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|PaginatorInterface $ordersToRemovePaginator;
    private MockObject|PaginatorInterface $ordersToChangeUserIdPaginator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->ordersToRemovePaginator = $this->createMock(PaginatorInterface::class);
        $this->ordersToChangeUserIdPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderRemoveAllGroupsOrdersService($this->orderRepository);
    }

    /**
     * @return Order[]
     */
    private function getOrdersToRemove(): array
    {
        $listOrders = $this->createMock(ListOrders::class);
        $product = $this->createMock(Product::class);

        return [
            Order::fromPrimitives(
                'order id 1',
                'group id 1',
                'user id 1',
                'order description 1',
                10,
                true,
                $listOrders,
                $product,
                null
            ),
            Order::fromPrimitives(
                'order id 2',
                'group id 1',
                'user id 2',
                'order description 2',
                20,
                true,
                $listOrders,
                $product,
                null
            ),
            Order::fromPrimitives(
                'order id 3',
                'group id 1',
                'user id 3',
                'order description 3',
                30,
                true,
                $listOrders,
                $product,
                null
            ),
        ];
    }

    /**
     * @return Order[]
     */
    private function getOrdersToChangeUserId(): array
    {
        $listOrders = $this->createMock(ListOrders::class);
        $product = $this->createMock(Product::class);

        return [
            Order::fromPrimitives(
                'order id 4',
                'group id 4',
                'user id 4',
                'order description 4',
                40,
                true,
                $listOrders,
                $product,
                null
            ),
            Order::fromPrimitives(
                'order id 5',
                'group id 5',
                'user id 5',
                'order description 5',
                50,
                true,
                $listOrders,
                $product,
                null
            ),
            Order::fromPrimitives(
                'order id 6',
                'group id 6',
                'user id 6',
                'order description 6',
                60,
                true,
                $listOrders,
                $product,
                null
            ),
        ];
    }

    /**
     * @param Order[] $orders
     *
     * @return Order[]
     */
    private function getOrdersToChangeUserIdAlreadyChanged(array $orders): array
    {
        $ordersToModify = array_map(
            fn (Order $order): Order => clone $order,
            $orders
        );

        $ordersToModify[0]->setUserId(ValueObjectFactory::createIdentifier('admin id 4'));
        $ordersToModify[1]->setUserId(ValueObjectFactory::createIdentifier('admin id 5'));
        $ordersToModify[2]->setUserId(ValueObjectFactory::createIdentifier('admin id 6'));

        return $ordersToModify;
    }

    /**
     * @return Identifier[]
     */
    private function getOrdersId(array $orders): array
    {
        return array_map(
            fn (Order $order): Identifier => $order->getId(),
            $orders
        );
    }

    private function getGroupsIdToRemove(): array
    {
        return [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
            ValueObjectFactory::createIdentifier('group id 3'),
        ];
    }

    private function getGroupsIdToChangeUserId(): array
    {
        return [
            ValueObjectFactory::createIdentifier('group id 4'),
            ValueObjectFactory::createIdentifier('group id 5'),
            ValueObjectFactory::createIdentifier('group id 6'),
        ];
    }

    private function getGroupsIdAndAdminToChangeUserId(): array
    {
        return [[
            'group_id' => ValueObjectFactory::createIdentifier('group id 4'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 4'),
        ], [
            'group_id' => ValueObjectFactory::createIdentifier('group id 5'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 5'),
        ], [
            'group_id' => ValueObjectFactory::createIdentifier('group id 6'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 6'),
        ]];
    }

    /** @test */
    public function itShouldRemoveGroupOrdersAndSetOrdersUserId(): void
    {
        $ordersToRemove = $this->getOrdersToRemove();
        $ordersIdToRemove = $this->getOrdersId($ordersToRemove);
        $ordersToChangeUserId = $this->getOrdersToChangeUserId();
        $ordersToChangeUserIdExpected = $this->getOrdersToChangeUserIdAlreadyChanged($ordersToChangeUserId);
        $ordersIdToChangeUserId = $this->getOrdersId($ordersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->orderRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input, $groupsIdToChangeUserId): bool {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->ordersToRemovePaginator,
                $this->ordersToChangeUserIdPaginator
            );

        $this->ordersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToRemove));

        $this->ordersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToChangeUserId));

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->ordersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->ordersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeOrdersUsersId(): void
    {
        $ordersToChangeUserId = $this->getOrdersToChangeUserId();
        $ordersIdToChangeUserId = $this->getOrdersId($ordersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $ordersToChangeUserIdExpected = $this->getOrdersToChangeUserIdAlreadyChanged($ordersToChangeUserId);
        $input = new OrderRemoveAllGroupsOrdersDto(
            [],
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findGroupsOrdersOrFail')
            ->with($groupsIdToChangeUserId)
            ->willReturn($this->ordersToChangeUserIdPaginator);

        $this->ordersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->ordersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToChangeUserId));

        $this->orderRepository
            ->expects($this->never())
            ->method('remove');

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->ordersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->ordersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeOrdersUsersIdGroupsIdToRemoveNotFound(): void
    {
        $ordersToChangeUserId = $this->getOrdersToChangeUserId();
        $ordersIdToChangeUserId = $this->getOrdersId($ordersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $ordersToChangeUserIdExpected = $this->getOrdersToChangeUserIdAlreadyChanged($ordersToChangeUserId);
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->orderRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input, $groupsIdToChangeUserId): bool {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(fn(): MockObject|PaginatorInterface => match ($orderRepositoryMatcher->getInvocationCount()) {
                1 => throw new DBNotFoundException(),
                2 => $this->ordersToChangeUserIdPaginator
            });

        $this->ordersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->ordersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToChangeUserId));

        $this->orderRepository
            ->expects($this->never())
            ->method('remove');

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->ordersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->ordersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailRemoveOrdersError(): void
    {
        $ordersToRemove = $this->getOrdersToRemove();
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findGroupsOrdersOrFail')
            ->with($input->groupsIdToRemoveOrders)
            ->willReturn($this->ordersToRemovePaginator);

        $this->ordersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToRemove));

        $this->ordersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove)
            ->willThrowException(new DBConnectionException());

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldOnlyRemoveGroupOrders(): void
    {
        $ordersToRemove = $this->getOrdersToRemove();
        $ordersIdToRemove = $this->getOrdersId($ordersToRemove);
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getGroupsIdToRemove(),
            [],
            null
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findGroupsOrdersOrFail')
            ->with($input->groupsIdToRemoveOrders)
            ->willReturn($this->ordersToRemovePaginator);

        $this->ordersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToRemove));

        $this->ordersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->ordersIdRemoved);
        $this->assertEmpty($return->ordersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyRemoveOrdersFromGroupsIdToRemoveOrdersToChangeUserIdGroupsIdNotFound(): void
    {
        $ordersToRemove = $this->getOrdersToRemove();
        $ordersIdToRemove = $this->getOrdersId($ordersToRemove);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->orderRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input, $groupsIdToChangeUserId): bool {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(fn(): MockObject|PaginatorInterface => match ($orderRepositoryMatcher->getInvocationCount()) {
                1 => $this->ordersToRemovePaginator,
                2 => throw new DBNotFoundException()
            });

        $this->ordersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToRemove));

        $this->ordersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->ordersIdRemoved);
        $this->assertEmpty($return->ordersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailChangingOrdersUserId(): void
    {
        $ordersToRemove = $this->getOrdersToRemove();
        $ordersToChangeUserId = $this->getOrdersToChangeUserId();
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $ordersToChangeUserIdExpected = $this->getOrdersToChangeUserIdAlreadyChanged($ordersToChangeUserId);
        $input = new OrderRemoveAllGroupsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->orderRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input, $groupsIdToChangeUserId): bool {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->ordersToRemovePaginator,
                $this->ordersToChangeUserIdPaginator
            );

        $this->ordersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToRemove));

        $this->ordersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($ordersToChangeUserId));

        $this->orderRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserIdExpected)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

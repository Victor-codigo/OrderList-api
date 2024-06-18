<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\ListOrdersRemoveAllGroupsListsOrdersService;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrderRemoveAllGroupsListsOrdersServiceTest extends TestCase
{
    private ListOrdersRemoveAllGroupsListsOrdersService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|PaginatorInterface $listsOrdersToRemovePaginator;
    private MockObject|PaginatorInterface $listsOrdersToChangeUserIdPaginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->listsOrdersToRemovePaginator = $this->createMock(PaginatorInterface::class);
        $this->listsOrdersToChangeUserIdPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersRemoveAllGroupsListsOrdersService($this->listOrdersRepository);
    }

    /**
     * @return ListOrders[]
     */
    private function getListsOrdersToRemove(): array
    {
        return [
            ListOrders::fromPrimitives(
                'order id 1',
                'group id 1',
                'user id 1',
                'listOrders name 1',
                'order description 1',
                null
            ),
            ListOrders::fromPrimitives(
                'order id 2',
                'group id 1',
                'user id 2',
                'listOrders name 2',
                'order description 2',
                null
            ),
            ListOrders::fromPrimitives(
                'order id 3',
                'group id 1',
                'user id 3',
                'listOrders name 3',
                'order description 3',
                null
            ),
        ];
    }

    /**
     * @return Order[]
     */
    private function getListsOrdersToChangeUserId(): array
    {
        return [
            ListOrders::fromPrimitives(
                'order id 4',
                'group id 2',
                'user id 4',
                'listOrders name 4',
                'order description 4',
                null
            ),
            ListOrders::fromPrimitives(
                'order id 5',
                'group id 2',
                'user id 5',
                'listOrders name 5',
                'order description 5',
                null
            ),
            ListOrders::fromPrimitives(
                'order id 5',
                'group id 2',
                'user id 5',
                'listOrders name 5',
                'order description 5',
                null
            ),
        ];
    }

    /**
     * @return Identifier[]
     */
    private function getListsOrdersId(array $orders): array
    {
        return array_map(
            fn (ListOrders $order) => $order->getId(),
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

    /** @test */
    public function itShouldRemoveGroupOrdersAndSetOrdersUserId(): void
    {
        $ordersToRemove = $this->getListsOrdersToRemove();
        $ordersIdToRemove = $this->getListsOrdersId($ordersToRemove);
        $ordersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $ordersIdToChangeUserId = $this->getListsOrdersId($ordersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input) {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($input->groupsIdToChangeListsOrdersUser, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->listsOrdersToRemovePaginator,
                $this->listsOrdersToChangeUserIdPaginator
            );

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->listOrdersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->listOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeOrdersUsersId(): void
    {
        $ordersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $ordersIdToChangeUserId = $this->getListsOrdersId($ordersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            [],
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($input->groupsIdToChangeListsOrdersUser)
            ->willReturn($this->listsOrdersToChangeUserIdPaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserId);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->listOrdersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->listOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeOrdersUsersIdGroupsIdToRemoveNotFound(): void
    {
        $ordersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $ordersIdToChangeUserId = $this->getListsOrdersId($ordersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input) {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($input->groupsIdToChangeListsOrdersUser, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(function () use ($orderRepositoryMatcher) {
                return match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => throw new DBNotFoundException(),
                    2 => $this->listsOrdersToChangeUserIdPaginator
                };
            });

        $this->listsOrdersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserId);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->listOrdersIdRemoved);
        $this->assertEquals($ordersIdToChangeUserId, $return->listOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailRemoveOrdersError(): void
    {
        $ordersToRemove = $this->getListsOrdersToRemove();
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($input->groupsIdToRemoveListsOrders)
            ->willReturn($this->listsOrdersToRemovePaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove)
            ->willThrowException(new DBConnectionException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldOnlyRemoveGroupOrders(): void
    {
        $ordersToRemove = $this->getListsOrdersToRemove();
        $ordersIdToRemove = $this->getListsOrdersId($ordersToRemove);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            [],
            null
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($input->groupsIdToRemoveListsOrders)
            ->willReturn($this->listsOrdersToRemovePaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->listOrdersIdRemoved);
        $this->assertEmpty($return->listOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyRemoveOrdersFromGroupsIdToRemoveOrdersToChangeUserIdGroupsIdNotFound(): void
    {
        $ordersToRemove = $this->getListsOrdersToRemove();
        $ordersIdToRemove = $this->getListsOrdersId($ordersToRemove);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input) {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($input->groupsIdToChangeListsOrdersUser, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(function () use ($orderRepositoryMatcher) {
                return match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->listsOrdersToRemovePaginator,
                    2 => throw new DBNotFoundException()
                };
            });

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($ordersIdToRemove, $return->listOrdersIdRemoved);
        $this->assertEmpty($return->listOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailChangingOrdersUserId(): void
    {
        $ordersToRemove = $this->getListsOrdersToRemove();
        $ordersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $orderRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($orderRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($orderRepositoryMatcher, $input) {
                match ($orderRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($input->groupsIdToChangeListsOrdersUser, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->listsOrdersToRemovePaginator,
                $this->listsOrdersToChangeUserIdPaginator
            );

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($ordersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($ordersToRemove);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($ordersToChangeUserId)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

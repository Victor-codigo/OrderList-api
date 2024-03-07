<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersAddOrder;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Application\ListOrdersAddOrder\Dto\OrderBoughtDto;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersAddOrder\Dto\ListOrdersAddOrderDto;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrdersListOrderNotFoundException;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrdersOrdersNotFoundException;
use ListOrders\Domain\Service\ListOrdersAddOrder\ListOrdersAddOrderService;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;

class ListOrdersAddOrderServiceTest extends TestCase
{
    private const LIST_ORDER_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDER_ID_IN_LIST_ORDER_1 = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const ORDER_ID_IN_LIST_ORDER_2 = '9a48ac5b-4571-43fd-ac80-28b08124ffb8';
    private const ORDER_ID_IN_LIST_ORDER_3 = '5cfe52e5-db78-41b3-9acd-c3c84924cb9b';

    private ListOrdersAddOrderService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository;
    private MockObject|OrderRepositoryInterface $ordersRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->listOrdersOrdersRepository = $this->createMock(ListOrdersOrdersRepositoryInterface::class);
        $this->ordersRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersAddOrderService(
            $this->listOrdersOrdersRepository,
            $this->listOrdersRepository,
            $this->ordersRepository
        );
    }

    /**
     * @return OrderBoughtDto[]
     */
    private function getOrdersBought(array $ordersId, array $bought): array
    {
        $ordersBought = [];
        foreach ($ordersId as $key => $orderId) {
            $ordersBought[] = new OrderBoughtDto(
                ValueObjectFactory::createIdentifier($orderId),
                $bought[$key]
            );
        }

        return $ordersBought;
    }

    /**
     * @param string[] $ordersId
     *
     * @return Order[]
     */
    private function getOrders(array $ordersId): array
    {
        $product = $this->createMock(Product::class);

        $orders = [];
        foreach ($ordersId as $key => $orderId) {
            $orderNumber = $key + 1;
            $orders[] = Order::fromPrimitives(
                $orderId,
                'user id '.$orderNumber,
                'group id '.$orderNumber,
                125.6,
                'order description '.$orderNumber,
                $product,
                null
            );
        }

        return $orders;
    }

    private function getListOrders(array $orders): ListOrders
    {
        $listOrders = ListOrders::fromPrimitives(
            'list order id',
            'group id',
            'user id',
            'list order name',
            'list order description',
            new \DateTime()
        )
        ->setOrders($orders);

        return $listOrders;
    }

    /**
     * @return ListOrdersOrders[]
     */
    private function getListOrdersOrders(array $ordersId): array
    {
        $orders = $this->getOrders($ordersId);
        $listOrders = $this->getListOrders($orders);

        $listOrdersOrders = [];
        foreach ($orders as $key => $order) {
            $listOrdersOrdersNumber = $key + 1;
            $listOrdersOrders[] = ListOrdersOrders::fromPrimitives(
                'list orders orders id '.$listOrdersOrdersNumber,
                $order->getId()->getValue(),
                $listOrders->getId()->getValue(),
                false,
                $listOrders,
                $order
            );
        }

        return $listOrdersOrders;
    }

    /**
     * @param OrderBoughtDto[] $ordersBought
     *
     * @return ListOrdersOrders[]
     */
    private function getListOrdersOrdersExpected(array $ordersBought): array
    {
        $ordersId = array_map(
            fn (OrderBoughtDto $orderBought) => $orderBought->orderId->getValue(),
            $ordersBought
        );
        $ordersBought = array_map(
            fn (OrderBoughtDto $orderBought) => $orderBought->bought,
            $ordersBought
        );

        $orders = $this->getOrders($ordersId);
        $listOrders = $this->getListOrders($orders);

        $listOrdersOrders = [];
        foreach ($orders as $key => $order) {
            $listOrdersOrdersNumber = $key + 1;
            $listOrdersOrders[] = ListOrdersOrders::fromPrimitives(
                'list orders orders id '.$listOrdersOrdersNumber,
                $order->getId()->getValue(),
                $listOrders->getId()->getValue(),
                $ordersBought[$key],
                $listOrders,
                $order
            );
        }

        return $listOrdersOrders;
    }

    /**
     * @param OrderBoughtDto[] $ordersBought
     */
    private function createListOrdersAddOrderDto(array $ordersBought): ListOrdersAddOrderDto
    {
        return new ListOrdersAddOrderDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $ordersBought
        );
    }

    private function assertListOrdersOrderIsOk(ListOrdersOrders $listOrdersOrdersExpected, ListOrdersOrders $listOrdersOrdersActual): void
    {
        $this->assertEquals($listOrdersOrdersExpected->getListOrdersId(), $listOrdersOrdersActual->getListOrdersId());
        $this->assertEquals($listOrdersOrdersExpected->getListOrdersId(), $listOrdersOrdersActual->getListOrdersId());
        $this->assertEquals($listOrdersOrdersExpected->getBought(), $listOrdersOrdersActual->getBought());
    }

    /**
     * @param ListOrdersOrders[] $listOrdersOrdersExpected
     * @param ListOrdersOrders[] $listOrdersOrdersActual
     */
    private function assertListOrdersOrdersAreOk(array $listOrdersOrdersExpected, array $listOrdersOrdersActual): void
    {
        foreach ($listOrdersOrdersActual as $listOrdersOrderActual) {
            $listOrdersOrdersExpectedByOrderId = array_combine(
                array_map(
                    fn (ListOrdersOrders $listOrdersOrders) => $listOrdersOrders->getOrderId()->getValue(),
                    $listOrdersOrdersExpected
                ),
                $listOrdersOrdersExpected
            );

            $this->assertListOrdersOrderIsOk($listOrdersOrdersExpectedByOrderId[$listOrdersOrderActual->getOrderId()->getValue()], $listOrdersOrderActual);
        }
    }

    private function getOrdersBoughtIds(): array
    {
        return [
                self::ORDER_ID_IN_LIST_ORDER_1,
                self::ORDER_ID_IN_LIST_ORDER_2,
                self::ORDER_ID_IN_LIST_ORDER_3,
        ];
    }

    private function getOrdersBoughtRepeatedIds(): array
    {
        return [
            self::ORDER_ID_IN_LIST_ORDER_1,
            self::ORDER_ID_IN_LIST_ORDER_2,
            self::ORDER_ID_IN_LIST_ORDER_3,
            self::ORDER_ID_IN_LIST_ORDER_1,
            self::ORDER_ID_IN_LIST_ORDER_1,
            self::ORDER_ID_IN_LIST_ORDER_2,
        ];
    }

    private function getListOrdersOrdersId(): array
    {
        return [
            'list orders id 1',
            'list orders id 2',
        ];
    }

    /** @test */
    public function itShouldCreateAListOrdersOrder(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBought = $this->getOrdersBought($ordersBoughtId, [true, false, true]);
        $listOrdersOrdersId = $this->getLIstOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $orders = $this->getOrders(array_merge($ordersBoughtId, $listOrdersOrdersId));
        $listOrdersOrdersExpected = $this->getListOrdersOrdersExpected($ordersBought);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->exactly(count($ordersBoughtId)))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listOrdersOrdersActual) => $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $listOrdersOrdersActual) || true));

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders),
                new \ArrayIterator($orders)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(count($ordersBoughtId), $return);
        $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $return);
    }

    /** @test */
    public function itShouldCreateAListOrdersOrderSomeOrdersAreRepeated(): void
    {
        $ordersBoughtRepeatedIds = $this->getOrdersBoughtRepeatedIds();
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBought = $this->getOrdersBought($ordersBoughtRepeatedIds, [true, false, true, false, false, true]);
        $listOrdersOrdersId = $this->getListOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $orders = $this->getOrders(array_merge($ordersBoughtId, $listOrdersOrdersId));
        $listOrdersOrdersExpected = $this->getListOrdersOrdersExpected($ordersBought);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->exactly(count($ordersBoughtId)))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listOrdersOrdersActual) => $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $listOrdersOrdersActual) || true));

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders),
                new \ArrayIterator($orders)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(count($ordersBoughtId), $return);
        $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $return);
    }

    /** @test */
    public function itShouldCreateAListOrdersOrderSomeOrdersIdAreNotInDatabase(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBoughtAndNotInDataBaseId = array_merge($ordersBoughtId, [
            'id not in database 1',
            'id not in database 2',
        ]);
        $ordersBought = $this->getOrdersBought($ordersBoughtAndNotInDataBaseId, [true, false, true, false, false]);
        $listOrdersOrdersId = $this->getListOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $orders = $this->getOrders(array_merge($ordersBoughtId, $listOrdersOrdersId));
        $listOrdersOrdersExpected = $this->getListOrdersOrdersExpected($ordersBought);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listOrdersOrdersActual) => $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $listOrdersOrdersActual) || true));

        $this->listOrdersOrdersRepository
            ->expects($this->exactly(3))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtAndNotInDataBaseId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders),
                new \ArrayIterator($orders)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(count($ordersBoughtId), $return);
        $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $return);
    }

    /** @test */
    public function itShouldCreateAListOrdersOrderSomeOrdersIdAreAlreadyInTheListOfOrders(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBoughtAndOrdersIdAlreadyInListOrders = array_merge($ordersBoughtId, [
            'list orders id 1',
            'list orders id 2',
        ]);
        $ordersBought = $this->getOrdersBought($ordersBoughtAndOrdersIdAlreadyInListOrders, [true, false, true, false, false]);
        $listOrdersOrdersId = $this->getListOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $orders = $this->getOrders(array_merge($ordersBoughtId, $listOrdersOrdersId));
        $listOrdersOrdersExpected = $this->getListOrdersOrdersExpected($ordersBought);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listOrdersOrdersActual) => $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $listOrdersOrdersActual) || true));

        $this->listOrdersOrdersRepository
            ->expects($this->exactly(3))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtAndOrdersIdAlreadyInListOrders, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders),
                new \ArrayIterator($orders)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(count($ordersBoughtId), $return);
        $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $return);
    }

    /** @test */
    public function itShouldCreateAListOrdersOrderAllOrdersIdAreAlreadyInTheListOfOrders(): void
    {
        $ordersBoughtId = [
            'list orders id 1',
            'list orders id 2',
        ];
        $ordersBought = $this->getOrdersBought($ordersBoughtId, [true, false]);
        $listOrdersOrdersId = $this->getListOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $orders = $this->getOrders(array_merge($ordersBoughtId, $listOrdersOrdersId));
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('generateId');

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders),
                new \ArrayIterator($orders)
            );

        $this->expectException(ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldCreateAListOrdersOrderListOrdersIsEmpty(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBought = $this->getOrdersBought($ordersBoughtId, [true, false, true]);
        $orders = $this->getOrders($ordersBoughtId);
        $listOrders = $this->getListOrders($orders);
        $listOrdersOrdersExpected = $this->getListOrdersOrdersExpected($ordersBought);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listOrdersOrdersActual) => $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $listOrdersOrdersActual) || true));

        $this->listOrdersOrdersRepository
            ->expects($this->exactly(3))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtId, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrders]),
                new \ArrayIterator($orders)
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(count($ordersBoughtId), $return);
        $this->assertListOrdersOrdersAreOk($listOrdersOrdersExpected, $return);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersOrderListOrderNotFound(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBought = $this->getOrdersBought($ordersBoughtId, [true, false, true]);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('findListOrderOrdersByIdOrFail');

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('generateId');

        $this->ordersRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(ListOrdersAddOrdersListOrderNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersOrdersNotFound(): void
    {
        $ordersBoughtId = $this->getOrdersBoughtIds();
        $ordersBought = $this->getOrdersBought($ordersBoughtId, [true, false, true]);
        $listOrdersOrdersId = $this->getListOrdersOrdersId();
        $listOrdersOrders = $this->getListOrdersOrders($listOrdersOrdersId);
        $input = $this->createListOrdersAddOrderDto($ordersBought);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(...$ordersBoughtId);

        $this->ordersRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($ordersBoughtId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$listOrdersOrders[0]->getListOrders()]),
                new \ArrayIterator($listOrdersOrders)
            );

        $this->expectException(ListOrdersAddOrdersOrdersNotFoundException::class);
        $this->object->__invoke($input);
    }
}

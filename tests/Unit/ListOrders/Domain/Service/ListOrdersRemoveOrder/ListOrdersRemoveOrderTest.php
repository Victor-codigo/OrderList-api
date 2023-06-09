<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersRemoveOrder;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderDto;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\ListOrdersRemoveOrderService;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveOrderTest extends TestCase
{
    private ListOrdersRemoveOrderService $object;
    private MockObject|ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersOrdersRepository = $this->createMock(ListOrdersOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersRemoveOrderService($this->listOrdersOrdersRepository);
    }

    private function getLisOrdersOrders(): array
    {
        $listOrders = $this->createMock(ListOrders::class);
        $order = $this->createMock(Order::class);

        return [
            ListOrdersOrders::fromPrimitives(
                'list order id 1',
                'order id 1',
                'list orders 1',
                false,
                $listOrders,
                $order
            ),
            ListOrdersOrders::fromPrimitives(
                'list order id 2',
                'order id 2',
                'list orders 2',
                false,
                $listOrders,
                $order
            ),
            ListOrdersOrders::fromPrimitives(
                'list order id 3',
                'order id 3',
                'list orders 3',
                false,
                $listOrders,
                $order
            ),
        ];
    }

    private function getListOrdersRemoveOrderDto(): ListOrdersRemoveOrderDto
    {
        return new ListOrdersRemoveOrderDto(
            ValueObjectFactory::createIdentifier('list orders id'),
            ValueObjectFactory::createIdentifier('group id'),
            [
                ValueObjectFactory::createIdentifier('order id 1'),
                ValueObjectFactory::createIdentifier('order id 2'),
                ValueObjectFactory::createIdentifier('order id 3'),
            ]
        );
    }

    /** @test */
    public function itShouldRemoveSomeOrders(): void
    {
        $listOrdersOrders = $this->getLisOrdersOrders();
        $input = $this->getListOrdersRemoveOrderDto();

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with($input->listOrdersId, $input->groupId, $input->ordersId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listOrdersOrders);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($listOrdersOrders));

        $return = $this->object->__invoke($input);

        $this->assertEquals($listOrdersOrders, $return);
    }

    /** @test */
    public function itShouldFailRemoveSomeOrdersOrdersNotFound(): void
    {
        $input = $this->getListOrdersRemoveOrderDto();

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with($input->listOrdersId, $input->groupId, $input->ordersId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemoveSomeOrdersErrorRemoving(): void
    {
        $listOrdersOrders = $this->getLisOrdersOrders();
        $input = $this->getListOrdersRemoveOrderDto();

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersByIdOrFail')
            ->with($input->listOrdersId, $input->groupId, $input->ordersId)
            ->willReturn($this->paginator);

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listOrdersOrders)
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($listOrdersOrders));

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

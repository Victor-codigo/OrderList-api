<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemove\Dto\ListOrdersRemoveDto;
use ListOrders\Domain\Service\ListOrdersRemove\ListOrdersRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveServiceTest extends TestCase
{
    private ListOrdersRemoveService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersRemoveService($this->listOrdersRepository);
    }

    /**
     * @return ListOrders[]
     */
    private function getListOrders(): array
    {
        return [
            ListOrders::fromPrimitives(
                'list orders id 1',
                'group id 1',
                'user id',
                'list orders name 1',
                'list orders description 1',
                new \DateTime(),
            ),
            ListOrders::fromPrimitives(
                'list orders id 2',
                'group id 1',
                'user id',
                'list orders name 2',
                'list orders description 2',
                new \DateTime(),
            ),
            ListOrders::fromPrimitives(
                'list orders id 3',
                'group id 1',
                'user id',
                'list orders name 3',
                'list orders description 3',
                new \DateTime(),
            ),
        ];
    }

    /** @test */
    public function itShouldRemoveListsOrders(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('group id'),
            [
                ValueObjectFactory::createIdentifier('list orders id 1'),
                ValueObjectFactory::createIdentifier('list orders id 2'),
                ValueObjectFactory::createIdentifier('list orders id 3'),
            ],
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listsOrdersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listOrders);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($listOrders));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $return = $this->object->__invoke($input);

        $this->assertEquals($listOrders, $return);
    }

    /** @test */
    public function itShouldRemoveListsOrdersThatExists(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('group id'),
            [
                ValueObjectFactory::createIdentifier('list orders id 1'),
                ValueObjectFactory::createIdentifier('list orders id 2'),
                ValueObjectFactory::createIdentifier('list orders id 3'),
            ],
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listsOrdersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$listOrders[0], $listOrders[2]]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrders[0], $listOrders[2]]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $return = $this->object->__invoke($input);

        $this->assertEquals([$listOrders[0], $listOrders[2]], $return);
    }

    /** @test */
    public function itShouldFailRemovingListsOrdersNotFound(): void
    {
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('group id'),
            [
                ValueObjectFactory::createIdentifier('list orders id 1'),
                ValueObjectFactory::createIdentifier('list orders id 2'),
                ValueObjectFactory::createIdentifier('list orders id 3'),
            ],
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listsOrdersId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingListsOrdersRemoveError(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('group id'),
            [
                ValueObjectFactory::createIdentifier('list orders id 1'),
                ValueObjectFactory::createIdentifier('list orders id 2'),
                ValueObjectFactory::createIdentifier('list orders id 3'),
            ],
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listsOrdersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrders]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

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

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            'list orders id',
            'group id',
            'user id',
            'list orders name',
            'list orders description',
            new \DateTime()
        );
    }

    /** @test */
    public function itShouldRemoveAListOrder(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('list orders id'),
            ValueObjectFactory::createIdentifier('group id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listOrders);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrders]));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $return = $this->object->__invoke($input);

        $this->assertEquals($listOrders, $return);
    }

    /** @test */
    public function itShouldFailRemovingAListOrderNotFound(): void
    {
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('list orders id'),
            ValueObjectFactory::createIdentifier('group id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
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
    public function itShouldFailRemovingAListOrderRemoveError(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersRemoveDto(
            ValueObjectFactory::createIdentifier('list orders id'),
            ValueObjectFactory::createIdentifier('group id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
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
            ->with(1, 1);

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

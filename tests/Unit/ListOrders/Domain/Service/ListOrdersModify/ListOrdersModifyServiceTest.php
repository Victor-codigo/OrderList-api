<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersModify\Dto\ListOrdersModifyDto;
use ListOrders\Domain\Service\ListOrdersModify\ListOrdersModifyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersModifyServiceTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private ListOrdersModifyService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersToRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersToRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersModifyService($this->listOrdersToRepository);
    }

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
            self::GROUP_ID,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'list orders name',
            'list orders description',
            new \DateTime()
        );
    }

    private function assertListOrdersIsOk(array $listsOrdersExpected, array $listsOrdersActual): void
    {
        foreach ($listsOrdersExpected as $key => $listOrdersExpected) {
            $this->assertEquals($listOrdersExpected->getId(), $listsOrdersActual[$key]->getId());
            $this->assertEquals($listOrdersExpected->getGroupId(), $listsOrdersActual[$key]->getGroupId());
            $this->assertEquals($listOrdersExpected->getUserId(), $listsOrdersActual[$key]->getUserId());
            $this->assertEquals($listOrdersExpected->getName(), $listsOrdersActual[$key]->getName());
            $this->assertEquals($listOrdersExpected->getDescription(), $listsOrdersActual[$key]->getDescription());
            $this->assertEquals($listOrdersExpected->getDateToBuy(), $listsOrdersActual[$key]->getDateToBuy());
        }
    }

    /** @test */
    public function itShouldModifyTheListOrder(): void
    {
        $listOrder = $this->getListOrders();
        $input = new ListOrdersModifyDto(
            ValueObjectFactory::createIdentifier('8a24edd8-b8e0-4609-b1e6-a67c6c122d61'),
            ValueObjectFactory::createIdentifier('8a24edd8-b8e0-4609-b1e6-a67c6c122d61'),
            ValueObjectFactory::createIdentifier($listOrder->getId()->getValue()),
            ValueObjectFactory::createNameWithSpaces('list orders name modified'),
            ValueObjectFactory::createDescription('list orders description modified'),
            ValueObjectFactory::createDateNowToFuture(new \DateTime())
        );
        $listOrderExpected = new ListOrders(
            $listOrder->getId(),
            $listOrder->getGroupId(),
            $input->userId,
            $input->name,
            $input->description,
            $input->dateToBuy
        );

        $this->listOrdersToRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersToRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $listsOrdersActual) => $this->assertListOrdersIsOk([$listOrderExpected], $listsOrdersActual) || true));

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrder]));

        $return = $this->object->__invoke($input);

        $this->assertListOrdersIsOk([$listOrderExpected], [$return]);
    }

    /** @test */
    public function itShouldFailModifyingTheListOrderListOrderNotFound(): void
    {
        $listOrder = $this->getListOrders();
        $input = new ListOrdersModifyDto(
            ValueObjectFactory::createIdentifier('8a24edd8-b8e0-4609-b1e6-a67c6c122d61'),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier($listOrder->getId()->getValue()),
            ValueObjectFactory::createNameWithSpaces('list orders name modified'),
            ValueObjectFactory::createDescription('list orders description modified'),
            ValueObjectFactory::createDateNowToFuture(new \DateTime())
        );

        $this->listOrdersToRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersToRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheListOrderSaveError(): void
    {
        $listOrder = $this->getListOrders();
        $input = new ListOrdersModifyDto(
            ValueObjectFactory::createIdentifier('8a24edd8-b8e0-4609-b1e6-a67c6c122d61'),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier($listOrder->getId()->getValue()),
            ValueObjectFactory::createNameWithSpaces('list orders name modified'),
            ValueObjectFactory::createDescription('list orders description modified'),
            ValueObjectFactory::createDateNowToFuture(new \DateTime())
        );

        $this->listOrdersToRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersToRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrder]));

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

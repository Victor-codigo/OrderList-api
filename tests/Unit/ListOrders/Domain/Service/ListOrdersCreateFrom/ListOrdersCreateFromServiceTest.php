<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersCreateFrom;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Dto\ListOrdersCreateFromDto;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromListOrdersIdNotFoundException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromNameAlreadyExistsException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\ListOrdersCreateFromService;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class ListOrdersCreateFromServiceTest extends TestCase
{
    private const string LIST_ORDERS_ID_OLD = 'list orders old';
    private const string LIST_ORDERS_ID_NEW = 'list orders new';
    private const string ORDER_ID_NEW = 'order id new';

    private ListOrdersCreateFromService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|OrderRepositoryInterface $ordersRepository;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->ordersRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersCreateFromService($this->listOrdersRepository, $this->ordersRepository);
    }

    private function getListOrders(string $listOrdersName, bool $orders): MockObject|ListOrders
    {
        /** @var MockObject|ListOrders $listOrders */
        $listOrders = $this->getMockBuilder(ListOrders::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                ValueObjectFactory::createIdentifier("{$listOrdersName}  id"),
                ValueObjectFactory::createIdentifier("{$listOrdersName} group id"),
                ValueObjectFactory::createIdentifier("{$listOrdersName} user id"),
                ValueObjectFactory::createNameWithSpaces("{$listOrdersName} name"),
                ValueObjectFactory::createDescription("{$listOrdersName} description"),
                ValueObjectFactory::createDateNowToFuture(null),
            ])
            ->onlyMethods([])
            ->getMock();

        if ($orders) {
            $listOrders->setOrders($this->getOrders($listOrders, 'order old'));
        }

        return $listOrders;
    }

    private function getOrders(MockObject|ListOrders $listOrders, string $orderName): array
    {
        $product1 = $this->createMock(Product::class);
        $product2 = $this->createMock(Product::class);
        $shop1 = $this->createMock(Shop::class);
        $shop2 = $this->createMock(Shop::class);

        return [
            Order::fromPrimitives(
                "{$orderName} 1 id",
                "{$orderName} 1 group id",
                "{$orderName} 1 user id",
                "{$orderName} 1 description",
                1,
                true,
                $listOrders,
                $product1,
                $shop1
            ),
            Order::fromPrimitives(
                "{$orderName} 2 id",
                "{$orderName} 2 group id",
                "{$orderName} 2 user id",
                "{$orderName} 2 description",
                2,
                false,
                $listOrders,
                $product2,
                $shop2
            ),
            Order::fromPrimitives(
                "{$orderName} 3 id",
                "{$orderName} 3 group id",
                "{$orderName} 3 user id",
                "{$orderName} 3 description",
                3,
                true,
                $listOrders,
                $product1,
                $shop2
            ),
        ];
    }

    private function assertOrderCopiedIsOk(Order $orderNew, Order $orderOld, ListOrders $listOrdersOld, Identifier $userId): void
    {
        $this->assertEquals(self::ORDER_ID_NEW, $orderNew->getId()->getValue());
        $this->assertEquals($orderOld->getGroupId()->getValue(), $orderNew->getGroupId()->getValue());
        $this->assertEquals($userId->getValue(), $orderNew->getUserId()->getValue());
        $this->assertEquals($orderOld->getDescription()->getValue(), $orderNew->getDescription()->getValue());
        $this->assertEquals($orderOld->getAmount()->getValue(), $orderNew->getAmount()->getValue());
        $this->assertFalse($orderNew->getBought());
        $this->assertNotEquals($orderOld->getListOrders(), $orderNew->getListOrders());
        $this->assertEquals($orderOld->getProduct(), $orderNew->getProduct());
        $this->assertEquals($orderOld->getShop(), $orderNew->getShop());
    }

    private function assertListOrdersNewIsOk(ListOrders $listOrdersNew, ListOrders $listOrdersOld, Identifier $userId): void
    {
        $this->assertNotEquals($listOrdersOld->getId()->getValue(), $listOrdersNew->getId()->getValue());
        $this->assertEquals($listOrdersOld->getGroupId()->getValue(), $listOrdersNew->getGroupId()->getValue());
        $this->assertEquals($userId->getValue(), $listOrdersNew->getUserId());
        $this->assertEquals($listOrdersOld->getDescription()->getValue(), $listOrdersNew->getDescription()->getValue());
        $this->assertNull($listOrdersNew->getDateToBuy()->getValue());
    }

    private function assertListOrdersOrdersNewIsOk(ListOrders $listOrdersOld, ListOrders $listOrdersNew, Identifier $userId): void
    {
        $this->assertListOrdersNewIsOk($listOrdersNew, $listOrdersOld, $userId);
        $ordersOld = $listOrdersOld->getOrders();

        /** @var Order $orderNew */
        foreach ($listOrdersNew->getOrders() as $key => $orderNew) {
            $this->assertOrderCopiedIsOk($orderNew, $ordersOld->get($key), $listOrdersOld, $userId);
        }
    }

    public function createListOrdersDataProvider(): iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @test
     *
     * @dataProvider createListOrdersDataProvider
     * */
    public function itShouldCreateAListOrdersFromOtherListOrders($hasOrders): void
    {
        $listOrdersOld = $this->getListOrders(self::LIST_ORDERS_ID_OLD, $hasOrders);
        $listOrdersOldNumOrders = count($listOrdersOld->getOrders());
        $input = new ListOrdersCreateFromDto(
            ValueObjectFactory::createIdentifier('list orders old id'),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createIdentifier('user id'),
            ValueObjectFactory::createNameWithSpaces('list orders new name'),
        );
        $filterText = ValueObjectFactory::createFilter(
            'listOrdersName',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $input->name
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersIdCreateFrom], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByListOrdersNameFilterOrFail')
            ->with($input->groupId, $filterText, true)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->exactly($listOrdersOldNumOrders + 1))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(
                self::LIST_ORDERS_ID_NEW,
                ...array_fill(0, $listOrdersOldNumOrders, self::ORDER_ID_NEW),
            );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('saveListOrdersAndOrders')
            ->with($this->callback(fn (ListOrders $listOrdersNew) => $this->assertListOrdersOrdersNewIsOk($listOrdersOld, $listOrdersNew, $input->userId) || true));

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrdersOld]));

        $return = $this->object->__invoke($input);

        $this->assertListOrdersOrdersNewIsOk($listOrdersOld, $return, $input->userId);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersFromOtherListOrdersNameAlreadyExists(): void
    {
        $listOrdersOld = $this->getListOrders(self::LIST_ORDERS_ID_OLD, true);
        $input = new ListOrdersCreateFromDto(
            ValueObjectFactory::createIdentifier('list orders old id'),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createIdentifier('user id'),
            ValueObjectFactory::createNameWithSpaces('list orders new name'),
        );
        $filterText = ValueObjectFactory::createFilter(
            'listOrdersName',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $input->name
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersIdCreateFrom], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByListOrdersNameFilterOrFail')
            ->with($input->groupId, $filterText, true)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('generateId');

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('saveListOrdersAndOrders');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrdersOld]));

        $this->expectException(ListOrdersCreateFromNameAlreadyExistsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersFromOtherListOrdersNotFound(): void
    {
        $input = new ListOrdersCreateFromDto(
            ValueObjectFactory::createIdentifier('list orders old id'),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createIdentifier('user id'),
            ValueObjectFactory::createNameWithSpaces('list orders new name'),
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersIdCreateFrom], $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByListOrdersNameFilterOrFail');

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('generateId');

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('saveListOrdersAndOrders');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(ListOrdersCreateFromListOrdersIdNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersFromOtherErrorSaving(): void
    {
        $listOrdersOld = $this->getListOrders(self::LIST_ORDERS_ID_OLD, true);
        $listOrdersOldNumOrders = count($listOrdersOld->getOrders());
        $input = new ListOrdersCreateFromDto(
            ValueObjectFactory::createIdentifier('list orders old id'),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createIdentifier('user id'),
            ValueObjectFactory::createNameWithSpaces('list orders new name'),
        );
        $filterText = ValueObjectFactory::createFilter(
            'listOrdersName',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $input->name
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersIdCreateFrom], $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByListOrdersNameFilterOrFail')
            ->with($input->groupId, $filterText, true)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->exactly($listOrdersOldNumOrders + 1))
            ->method('generateId')
            ->willReturnOnConsecutiveCalls(
                self::LIST_ORDERS_ID_NEW,
                ...array_fill(0, $listOrdersOldNumOrders, self::ORDER_ID_NEW),
            );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('saveListOrdersAndOrders')
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrdersOld]));

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}

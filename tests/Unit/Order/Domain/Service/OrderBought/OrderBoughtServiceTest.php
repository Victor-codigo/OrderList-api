<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderBought;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderBought\Dto\OrderBoughtDto;
use Order\Domain\Service\OrderBought\OrderBoughtService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class OrderBoughtServiceTest extends TestCase
{
    private OrderBoughtService $object;
    private MockObject&OrderRepositoryInterface $orderRepository;
    /**
     * @var MockObject&PaginatorInterface<int, Order>
     */
    private MockObject&PaginatorInterface $paginator;
    private MockObject&Order $order;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->order = $this->createMock(Order::class);
        $this->object = new OrderBoughtService($this->orderRepository);
    }

    public function getOrder(): Order
    {
        /** @var MockObject&ListOrders $listOrders */
        $listOrders = $this->createMock(ListOrders::class);
        /** @var MockObject&Product $product */
        $product = $this->createMock(Product::class);
        /** @var (MockObject&Shop)|null $shop */
        $shop = $this->createMock(Shop::class);

        return Order::fromPrimitives(
            'order id',
            'group id',
            'user id',
            'order description',
            10,
            true,
            $listOrders,
            $product,
            $shop
        );
    }

    /**
     * @return iterable<bool[]>
     */
    public static function boughtDataProvider(): iterable
    {
        yield [true];
        yield [false];
    }

    #[DataProvider('boughtDataProvider')]
    #[Test]
    public function itShouldSetBought(bool $bought): void
    {
        $input = new OrderBoughtDto(
            ValueObjectFactory::createIdentifier('order id'),
            ValueObjectFactory::createIdentifier('group id'),
            $bought,
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->groupId, [$input->orderId], true)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with([$this->order]);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->order]));

        $this->order
            ->expects($this->once())
            ->method('setBought')
            ->with($input->bought);

        $return = $this->object->__invoke($input);

        $this->assertEquals($this->order, $return);
    }

    #[Test]
    public function itShouldFAilSettingBoughtOrderNotFound(): void
    {
        $input = new OrderBoughtDto(
            ValueObjectFactory::createIdentifier('order id'),
            ValueObjectFactory::createIdentifier('group id'),
            true,
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->groupId, [$input->orderId], true)
            ->willThrowException(new DBNotFoundException());

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->order
            ->expects($this->never())
            ->method('setBought');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}

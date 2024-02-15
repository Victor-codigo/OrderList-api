<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrdersGroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrdersGroupGetData\Dto\OrdersGroupGetDataDto;
use Order\Domain\Service\OrdersGroupGetData\OrdersGroupGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Shop\Domain\Model\Shop;

class OrdersGroupGetDataServiceTest extends TestCase
{
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRODUCT1_ID = '8b6d650b-7bb7-4850-bf25-36cda9bce801';
    private const PRODUCT2_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const SHOP1_ID = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';
    private const SHOP2_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const ORDER1_ID = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const ORDER2_ID = '9a48ac5b-4571-43fd-ac80-28b08124ffb8';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private OrdersGroupGetDataService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrdersGroupGetDataService($this->orderRepository);
    }

    private function assertOrderIsOk(Order $orderExpected, array $orderActual): void
    {
        $this->assertArrayHasKey('id', $orderActual);
        $this->assertArrayHasKey('user_id', $orderActual);
        $this->assertArrayHasKey('group_id', $orderActual);
        $this->assertArrayHasKey('description', $orderActual);
        $this->assertArrayHasKey('amount', $orderActual);
        $this->assertArrayHasKey('created_on', $orderActual);
        $this->assertArrayHasKey('product', $orderActual);
        $this->assertArrayHasKey('shop', $orderActual);
        $this->assertArrayHasKey('productShop', $orderActual);

        $this->assertArrayHasKey('id', $orderActual['product']);
        $this->assertArrayHasKey('name', $orderActual['product']);
        $this->assertArrayHasKey('description', $orderActual['product']);
        $this->assertArrayHasKey('image', $orderActual['product']);
        $this->assertArrayHasKey('created_on', $orderActual['product']);

        if (!$orderExpected->getShopId()->isNull()) {
            $this->assertArrayHasKey('id', $orderActual['shop']);
            $this->assertArrayHasKey('name', $orderActual['shop']);
            $this->assertArrayHasKey('description', $orderActual['shop']);
            $this->assertArrayHasKey('image', $orderActual['shop']);
            $this->assertArrayHasKey('created_on', $orderActual['shop']);

            $this->assertArrayHasKey('price', $orderActual['productShop']);
            $this->assertArrayHasKey('unit', $orderActual['productShop']);
        }

        $this->assertEquals($orderExpected->getId()->getValue(), $orderActual['id']);
        $this->assertEquals($orderExpected->getUserId()->getValue(), $orderActual['user_id']);
        $this->assertEquals($orderExpected->getGroupId()->getValue(), $orderActual['group_id']);
        $this->assertEquals($orderExpected->getDescription()->getValue(), $orderActual['description']);
        $this->assertEquals($orderExpected->getAmount()->getvalue(), $orderActual['amount']);

        $product = $orderExpected->getProduct();
        $this->assertEquals($product->getId()->getvalue(), $orderActual['product']['id']);
        $this->assertEquals($product->getName()->getValue(), $orderActual['product']['name']);
        $this->assertEquals($product->getDescription()->getValue(), $orderActual['product']['description']);
        $this->assertEquals($product->getImage()->getValue(), $orderActual['product']['image']);

        if (!$orderExpected->getShopId()->isNull()) {
            $shop = $orderExpected->getShop();
            $this->assertEquals($shop->getId()->getValue(), $orderActual['shop']['id']);
            $this->assertEquals($shop->getName(), $orderActual['shop']['name']);
            $this->assertEquals($shop->getDescription()->getValue(), $orderActual['shop']['description']);
            $this->assertEquals($shop->getImage()->getValue(), $orderActual['shop']['image']);

            /** @var ProductShop[] $productShop */
            $productShop = $orderExpected->getProduct()->getProductShop()->getValues();
            $this->assertEquals($productShop[0]->getPrice()->getValue(), $orderActual['productShop']['price']);
            $this->assertEquals($productShop[0]->getUnit()->getValue(), $orderActual['productShop']['unit']);
        }
    }

    /**
     * @return Order[]
     */
    private function getOrdersDataDb(): array
    {
        $product1 = Product::fromPrimitives(
            self::PRODUCT1_ID,
            self::GROUP_ID,
            'Juan Carlos',
            null,
            null
        );
        $product2 = Product::fromPrimitives(
            self::PRODUCT2_ID,
            self::GROUP_ID,
            'Maluela',
            'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
            null
        );

        $shop1 = Shop::fromPrimitives(
            self::SHOP1_ID,
            self::GROUP_ID,
            'Shop name 2',
            'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            null
        );
        $shop2 = Shop::fromPrimitives(
            self::SHOP2_ID,
            self::GROUP_ID,
            'Shop name 1',
            'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            null
        );

        $product1->setProductShop([
            ProductShop::fromPrimitives($product1, $shop2, 20.30, UNIT_MEASURE_TYPE::UNITS),
            ProductShop::fromPrimitives($product1, $shop1, 20.30, UNIT_MEASURE_TYPE::UNITS),
        ]);
        $product2->setProductShop([
            ProductShop::fromPrimitives($product2, $shop1, 10.5, UNIT_MEASURE_TYPE::UNITS),
            ProductShop::fromPrimitives($product2, $shop2, 10.5, UNIT_MEASURE_TYPE::UNITS),
        ]);

        return [
            self::ORDER1_ID => Order::fromPrimitives(
                self::ORDER1_ID,
                self::USER_ID,
                self::GROUP_ID,
                20.050,
                'order description 2',
                $product1,
                $shop1
            ),
            self::ORDER2_ID => Order::fromPrimitives(
                self::ORDER2_ID,
                self::USER_ID,
                self::GROUP_ID,
                10.200,
                'order description',
                $product2,
                $shop2
            ),
        ];
    }

    /**
     * @param Order[] $orders
     */
    private function setOrdersNoShop(array $orders): array
    {
        foreach ($orders as $order) {
            $order->setShop(null);
            $order->setShopId(ValueObjectFactory::createIdentifier(null));
            $order->getProduct()->setProductShop([]);
        }

        return $orders;
    }

    /**
     * @return Order[]
     */
    private function getOrdersDataExpected(): array
    {
        $ordersDataDb = $this->getOrdersDataDb();
        /** @var Order $order1 */
        $order1 = $ordersDataDb[self::ORDER1_ID];
        /** @var Order $order2 */
        $order2 = $ordersDataDb[self::ORDER2_ID];

        $order1->getProduct()->setProductShop([
            ProductShop::fromPrimitives($order1->getProduct(), $order1->getShop(), 20.30, UNIT_MEASURE_TYPE::UNITS),
        ]);
        $order2->getProduct()->setProductShop([
            ProductShop::fromPrimitives($order2->getProduct(), $order2->getShop(), 10.5, UNIT_MEASURE_TYPE::UNITS),
        ]);

        return $ordersDataDb;
    }

    /** @test */
    public function itShouldGetOrdersOfTheGroup(): void
    {
        $ordersData = $this->getOrdersDataDb();
        $ordersDataExpected = $this->getOrdersDataExpected();
        $input = new OrdersGroupGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersGroupOrFail')
            ->with($input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersData));

        $return = $this->object->__invoke($input);

        foreach ($ordersDataExpected as $orderExpected) {
            $this->assertOrderIsOk($orderExpected, $return[$orderExpected->getId()->getValue()]);
        }
    }

    /** @test */
    public function itShouldGetOrdersOfTheGroupProductHasNoShop(): void
    {
        $ordersDataDb = $this->setOrdersNoShop($this->getOrdersDataDb());
        $ordersDataExpected = $this->setOrdersNoShop($this->getOrdersDataExpected());

        $input = new OrdersGroupGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersGroupOrFail')
            ->with($input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersDataDb));

        $return = $this->object->__invoke($input);

        foreach ($ordersDataExpected as $orderExpected) {
            $this->assertOrderIsOk($orderExpected, $return[$orderExpected->getId()->getValue()]);
        }
    }

    /** @test */
    public function itShouldFailNoOrdersInTheGroup(): void
    {
        $input = new OrdersGroupGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersGroupOrFail')
            ->with($input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldGetPaginationTotalPages(): void
    {
        $ordersData = $this->getOrdersDataDb();
        $pagesTotal = 5;
        $input = new OrdersGroupGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersGroupOrFail')
            ->with($input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersData));

        $this->paginator
            ->expects($this->once())
            ->method('getPagesTotal')
            ->willReturn($pagesTotal);

        $this->object->__invoke($input);
        $return = $this->object->getPaginationTotalPages();

        $this->assertEquals($pagesTotal, $return);
    }

    /** @test */
    public function itShouldFailInvokeMethodNotCalled(): void
    {
        $this->expectException(LogicException::class);
        $this->object->getPaginationTotalPages();
    }
}

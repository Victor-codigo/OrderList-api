<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersGetOrders;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetOrders\Dto\ListOrdersGetOrdersDto;
use ListOrders\Domain\Service\ListOrdersGetOrders\ListOrdersGetOrdersService;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class ListOrdersGetOrdersServiceTest extends TestCase
{
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private ListOrdersGetOrdersService $object;
    private MockObject|ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersOrdersRepository = $this->createMock(ListOrdersOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersGetOrdersService($this->listOrdersOrdersRepository);
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

        $this->assertArrayHasKey('id', $orderActual['product']);
        $this->assertArrayHasKey('name', $orderActual['product']);
        $this->assertArrayHasKey('description', $orderActual['product']);
        $this->assertArrayHasKey('image', $orderActual['product']);
        $this->assertArrayHasKey('created_on', $orderActual['product']);

        $this->assertArrayHasKey('id', $orderActual['shop']);
        $this->assertArrayHasKey('name', $orderActual['shop']);
        $this->assertArrayHasKey('description', $orderActual['shop']);
        $this->assertArrayHasKey('image', $orderActual['shop']);
        $this->assertArrayHasKey('created_on', $orderActual['shop']);

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

        $shop = $orderExpected->getShop();
        $this->assertEquals($shop->getId()->getValue(), $orderActual['shop']['id']);
        $this->assertEquals($shop->getName(), $orderActual['shop']['name']);
        $this->assertEquals($shop->getDescription()->getValue(), $orderActual['shop']['description']);
        $this->assertEquals($shop->getImage()->getValue(), $orderActual['shop']['image']);
    }

    private function getOrdersData(): array
    {
        $product1 = Product::fromPrimitives(
            '8b6d650b-7bb7-4850-bf25-36cda9bce801',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Juan Carlos',
            null,
            null
        );
        $product2 = Product::fromPrimitives(
            'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Maluela',
            'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
            null
        );

        $shop1 = Shop::fromPrimitives(
            'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop name 2',
            'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            null
        );
        $shop2 = Shop::fromPrimitives(
            'e6c1d350-f010-403c-a2d4-3865c14630ec',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop name 1',
            'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            null
        );

        return [
            'a0b4760a-9037-477a-8b84-d059ae5ee7e9' => Order::fromPrimitives(
                'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                20.050,
                'order description 2',
                $product1,
                $shop1
            ),
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8' => Order::fromPrimitives(
                '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
                '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
                10.200,
                'order description',
                $product2,
                $shop2
            ),
        ];
    }

    /** @test */
    public function itShouldGetListOrdersData(): void
    {
        $ordersExpected = $this->getOrdersData();
        $input = new ListOrdersGetOrdersDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersDataByIdOrFail')
            ->with($input->listOrderId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersExpected));

        $return = $this->object->__invoke($input);

        foreach ($return as $order) {
            $this->assertOrderIsOk($ordersExpected[$order['id']], $order);
        }
    }

    /** @test */
    public function itShouldFailGettingListOrdersDataNotFound(): void
    {
        $input = new ListOrdersGetOrdersDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100)
        );

        $this->listOrdersOrdersRepository
            ->expects($this->once())
            ->method('findListOrderOrdersDataByIdOrFail')
            ->with($input->listOrderId)
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
}

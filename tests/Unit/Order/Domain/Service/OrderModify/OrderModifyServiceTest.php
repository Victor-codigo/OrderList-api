<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderModify\Dto\OrderModifyDto;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyShopIdNotFoundException;
use Order\Domain\Service\OrderModify\OrderModifyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderModifyServiceTest extends TestCase
{
    private const ORDER_ID = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRODUCT_ID = '8b6d650b-7bb7-4850-bf25-36cda9bce801';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private OrderModifyService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderModifyService($this->orderRepository, $this->productRepository, $this->shopRepository);
    }

    private function getProduct(): Product
    {
        return new Product(
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('product name modified'),
            ValueObjectFactory::createDescription('product description'),
            ValueObjectFactory::createPath('product image path modified'),
        );
    }

    private function getShop(): Shop
    {
        return new Shop(
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createNameWithSpaces('shop name modified'),
            ValueObjectFactory::createDescription('shop description modified'),
            ValueObjectFactory::createPath('shop image path modified')
        );
    }

    private function getOrderFromInput(OrderModifyDto $input, Product $product, Shop $shop): Order
    {
        return new Order(
            $input->orderId,
            $input->userId,
            $input->groupId,
            $input->description,
            $input->amount,
            $input->unit,
            $product,
            $shop,
        );
    }

    private function getOrder(Product $product, Shop $shop): Order
    {
        return new Order(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createDescription('order description'),
            ValueObjectFactory::createAmount(null),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
            $product,
            $shop
        );
    }

    private function assertOrderIsOk(Order $orderExpected, Order $order): void
    {
        $this->assertEquals($orderExpected->getId(), $order->getId());
        $this->assertEquals($orderExpected->getProductId(), $order->getProductId());
        $this->assertEquals($orderExpected->getShopId(), $order->getShopId());
        $this->assertEquals($orderExpected->getUserId(), $order->getUserId());
        $this->assertEquals($orderExpected->getGroupId(), $order->getGroupId());
        $this->assertEquals($orderExpected->getDescription(), $order->getDescription());
        $this->assertEquals($orderExpected->getAmount(), $order->getAmount());
        $this->assertEquals($orderExpected->getUnit(), $order->getUnit());
    }

    /** @test */
    public function itShouldModifyTheOrder(): void
    {
        $product = $this->getProduct();
        $shop = $this->getShop();
        $order = $this->getOrder($product, $shop);
        $input = new OrderModifyDto(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifierNullable(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createDescription('order description modified'),
            ValueObjectFactory::createAmount(10.3),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
        );
        $orderExpected = $this->getOrderFromInput($input, $product, $shop);

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId->toIdentifier()])
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with([$input->orderId], $input->groupId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $orderToSave) use ($orderExpected) {
                $this->assertCount(1, $orderToSave);
                $this->assertOrderIsOk($orderExpected, $orderToSave[0]);

                return true;
            }));

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$product]),
                new \ArrayIterator([$shop]),
                new \ArrayIterator([$order])
            );

        $return = $this->object->__invoke($input);

        $this->assertOrderIsOk($orderExpected, $return);
    }

    /** @test */
    public function itShouldFailModifyingTheOrderValidateProductNotFound(): void
    {
        $input = new OrderModifyDto(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifierNullable(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createDescription('order description modified'),
            ValueObjectFactory::createAmount(10.3),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(OrderModifyProductIdNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheOrderShopNotFound(): void
    {
        $product = $this->getProduct();
        $input = new OrderModifyDto(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifierNullable(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createDescription('order description modified'),
            ValueObjectFactory::createAmount(10.3),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId->toIdentifier()])
            ->willThrowException(new DBNotFoundException());

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$product]));

        $this->expectException(OrderModifyShopIdNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheOrderOrderNotFound(): void
    {
        $product = $this->getProduct();
        $shop = $this->getShop();
        $input = new OrderModifyDto(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifierNullable(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createDescription('order description modified'),
            ValueObjectFactory::createAmount(10.3),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId->toIdentifier()])
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with([$input->orderId], $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->orderRepository
            ->expects($this->never())
            ->method('save');

        $this->paginator
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$product]),
                new \ArrayIterator([$shop]),
            );

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModifyingTheOrderSavingError(): void
    {
        $product = $this->getProduct();
        $shop = $this->getShop();
        $order = $this->getOrder($product, $shop);
        $input = new OrderModifyDto(
            ValueObjectFactory::createIdentifier(self::ORDER_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifierNullable(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID),
            ValueObjectFactory::createDescription('order description modified'),
            ValueObjectFactory::createAmount(10.3),
            ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS),
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($input->groupId, [$input->productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, [$input->shopId->toIdentifier()])
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with([$input->orderId], $input->groupId)
            ->willReturn($this->paginator);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBUniqueConstraintException());

        $this->paginator
            ->expects($this->exactly(3))
            ->method('getIterator')
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$product]),
                new \ArrayIterator([$shop]),
                new \ArrayIterator([$order])
            );

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }
}

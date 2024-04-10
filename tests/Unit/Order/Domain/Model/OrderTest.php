<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Model;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class OrderTest extends TestCase
{
    private Order $object;

    protected function setUp(): void
    {
        parent::setUp();

        $listOrders = $this->createMock(ListOrders::class);
        $product = $this->createMock(Product::class);
        $shop = $this->createMock(Shop::class);
        $this->object = Order::fromPrimitives(
            '22cde739-cef7-40f4-a5b6-3be7018e80f5',
            '187de0f7-a017-48fb-a987-0c0f55a7bd95',
            '0fb81352-e16c-4769-892f-4a5cf33eeea1',
            'order description',
            1,
            false,
            $listOrders,
            $product,
            $shop
        );
    }

    /** @test */
    public function itShouldCloneOrderWithNewId(): void
    {
        $orderNewId = ValueObjectFactory::createIdentifier('order new id');
        $return = $this->object->cloneWithNewId($orderNewId);

        $this->assertEquals($orderNewId, $return->getId());
        $this->assertEquals($this->object->getListOrdersId(), $return->getListOrdersId());
        $this->assertEquals($this->object->getListOrders(), $return->getListOrders());
        $this->assertEquals($this->object->getProductId(), $return->getProductId());
        $this->assertEquals($this->object->getProduct(), $return->getProduct());
        $this->assertEquals($this->object->getShopId(), $return->getShopId());
        $this->assertEquals($this->object->getShop(), $return->getShop());
        $this->assertEquals($this->object->getUserId(), $return->getUserId());
        $this->assertEquals($this->object->getGroupId(), $return->getGroupId());
        $this->assertEquals($this->object->getDescription(), $return->getDescription());
        $this->assertEquals($this->object->getAmount(), $return->getAmount());
        $this->assertEquals($this->object->getBought(), $return->getBought());
        $this->assertEquals($this->object->getCreatedOn(), $return->getCreatedOn());
    }
}

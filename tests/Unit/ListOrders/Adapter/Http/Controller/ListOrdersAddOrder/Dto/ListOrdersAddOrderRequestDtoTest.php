<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Adapter\Http\Controller\ListOrdersAddOrder\Dto;

use ListOrders\Adapter\Http\Controller\ListOrdersAddOrder\Dto\ListOrdersAddOrderRequestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersAddOrderRequestDtoTest extends TestCase
{
    private const ORDER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const GROUP_ID = '1ab3504f-53fc-4229-85a8-6b4386109fb7';
    private const LIST_ORDERS_ID = '47bb985d-526c-483f-b167-c425c2725af4';
    private const ORDERS_NUM_MAX = 100;

    private function createRequest(array|null $attributes): ListOrdersAddOrderRequestDto
    {
        $request = new Request();
        $request->request = new ParameterBag();
        $request->request->set('list_orders_id', $attributes['list_orders_id']);
        $request->request->set('group_id', $attributes['group_id']);
        $request->request->set('orders', $attributes['orders']);

        return new ListOrdersAddOrderRequestDto($request);
    }

    /** @test */
    public function itShouldProcess100OrdersId(): void
    {
        $orders = array_fill(0, self::ORDERS_NUM_MAX, [
            'order_id' => self::ORDER_ID,
            'bought' => true,
        ]);
        $requestDto = $this->createRequest([
            'list_orders_id' => self::LIST_ORDERS_ID,
            'group_id' => self::GROUP_ID,
            'orders' => $orders,
        ]);

        $this->assertCount(self::ORDERS_NUM_MAX, $requestDto->orders);
        $this->assertEquals($orders, $requestDto->orders);
    }

    /** @test */
    public function itShouldProcessOnly100Of101OrdersId(): void
    {
        $orders = array_fill(0, self::ORDERS_NUM_MAX + 1, [
            'order_id' => self::ORDER_ID,
            'bought' => true,
        ]);
        $ordersExpected = array_fill(0, self::ORDERS_NUM_MAX, [
            'order_id' => self::ORDER_ID,
            'bought' => true,
        ]);

        $requestDto = $this->createRequest([
            'list_orders_id' => self::LIST_ORDERS_ID,
            'group_id' => self::GROUP_ID,
            'orders' => $orders,
        ]);

        $this->assertCount(self::ORDERS_NUM_MAX, $requestDto->orders);
        $this->assertEquals($ordersExpected, $requestDto->orders);
    }

    /** @test */
    public function itShouldProcessOrdersIsNull(): void
    {
        $requestDto = $this->createRequest([
            'list_orders_id' => self::LIST_ORDERS_ID,
            'group_id' => self::GROUP_ID,
            'orders' => null,
        ]);

        $this->assertEmpty($requestDto->orders);
    }
}

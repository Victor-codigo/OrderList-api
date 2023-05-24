<?php

declare(strict_types=1);

namespace Test\Unit\Order\Adapter\Http\Controller\OrderRemove\Dto;

use Order\Adapter\Http\Controller\OrderRemove\Dto\OrderRemoveRequestDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderRemoveRequestDtoTest extends TestCase
{
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
    ];

    private OrderRemoveRequestDto $object;
    private MockObject|RequestStack $request;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldSetOrdersIdToNullWhenRequestOrdersIsNull(): void
    {
        $content = [
            'group_id' => self::GROUP_ID,
            'orders_id' => null,
        ];
        $request = new Request(request: $content);
        $object = new OrderRemoveRequestDto($request);

        $this->assertEmpty($object->ordersId);
        $this->assertEquals(self::GROUP_ID, $object->groupId);
    }

    /** @test */
    public function itShouldSetOrdersIdToEqualOfTheRequest(): void
    {
        $content = [
            'group_id' => self::GROUP_ID,
            'orders_id' => self::ORDERS_ID,
        ];
        $request = new Request(request: $content);
        $object = new OrderRemoveRequestDto($request);

        $this->assertEquals(self::ORDERS_ID, $object->ordersId);
        $this->assertEquals(self::GROUP_ID, $object->groupId);
    }

    /** @test */
    public function itShouldSetOrdersIdToOnly100(): void
    {
        $ordersId = array_fill(0, 100, self::ORDERS_ID[0]);
        $ordersId[] = self::ORDERS_ID;
        $content = [
            'group_id' => self::GROUP_ID,
            'orders_id' => $ordersId,
        ];
        $request = new Request(request: $content);
        $object = new OrderRemoveRequestDto($request);

        $this->assertEquals(self::GROUP_ID, $object->groupId);
        $this->assertCount(100, $object->ordersId);

        foreach ($object->ordersId as $orderId) {
            $this->assertEquals(self::ORDERS_ID[0], $orderId);
        }
    }
}

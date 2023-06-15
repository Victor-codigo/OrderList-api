<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersAddOrder\Dto\ListOrdersAddOrderInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrderAddOrderInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateManyOrders(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => false,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $listOrdersId = null;
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $listOrdersId = 'wrong id';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = null;
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'wrong id';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailOrderIdNotSet(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'bought' => true,
            ],
        ];

        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $this->assertNull($object->ordersBought[0]->orderId->getValue());
    }

    /** @test */
    public function itShouldFailBoughtNotSet(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
            ],
        ];

        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $this->assertEquals(false, $object->ordersBought[0]->bought);
    }

    /** @test */
    public function itShouldFailOrdersEmpty(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailOrdersIdAreWrong(): void
    {
        $listOrdersId = 'da7bbcc5-0e4a-4ee9-bef4-64f65205b270';
        $groupId = 'b38b9754-f796-4c04-b9e1-a9889c18ac04';
        $orders = [
            [
                'order_id' => '105152fa-6cfa-42d6-8e6f-cd99a85ad5ef',
                'bought' => true,
            ],
            [
                'order_id' => 'wrong id',
                'bought' => false,
            ],
        ];
        $object = new ListOrdersAddOrderInputDto($this->userSession, $listOrdersId, $groupId, $orders);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }
}

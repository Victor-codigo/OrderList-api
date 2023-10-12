<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersGetOrders\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetOrders\Dto\ListOrdersGetOrdersInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersGetOrdersInputDtoTest extends TestCase
{
    private const LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';

    private MockObject|UserShared $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNullGroupId(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            null,
            self::LIST_ORDERS_ID,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailWrongGroupId(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            'wrong id',
            self::LIST_ORDERS_ID,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNullListOrderId(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_order_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailWrongListOrderId(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            'wrong id',
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_order_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNullPage(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            null,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailWrongPage(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            0,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailNullPageItems(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            1,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailWrongPageItems(): void
    {
        $object = new ListOrdersGetOrdersInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            1,
            0
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}

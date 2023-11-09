<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderModify\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderModify\Dto\OrderModifyInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderModifyInputDtoTest extends TestCase
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
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = null;
        $description = 'order description modified';
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateAmountIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = null;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateUnitIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 10;
        $unit = null;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIsNull(): void
    {
        $orderId = null;
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 10.6;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIsWrong(): void
    {
        $orderId = 'wong id';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 10.6;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateGroupIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = null;
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidateGroupIdIsWrong(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = 'wrong id';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateProductIdIsNull(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = null;
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidateProductIdIsWrong(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'wrong id';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateShopIdIsWrong(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = 'wrong id';
        $description = 'order description modified';
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailValidatingDescriptionIsTooLong(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = str_pad('', 501, 'p');
        $amount = 13.58;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailValidatingAmountIsNegative(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description';
        $amount = -1;
        $unit = UNIT_MEASURE_TYPE::UNITS->value;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['amount' => [VALIDATION_ERRORS::POSITIVE_OR_ZERO]], $return);
    }

    /** @test */
    public function itShouldFailValidatingUnitIsWrong(): void
    {
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description';
        $amount = 10;
        $unit = 'wrong unit';

        $object = new OrderModifyInputDto(
            $this->userSession,
            $orderId,
            $groupId,
            $productId,
            $shopId,
            $description,
            $amount,
            $unit
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertNull($object->unit->getValue());
    }
}

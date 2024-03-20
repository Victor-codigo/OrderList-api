<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderModify\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
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
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopIdIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = null;
        $description = 'order description modified';
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIdIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateAmountIdIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = null;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = null;
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 10.6;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIsWrong(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'wrong id';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 10.6;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateGroupIdIsNull(): void
    {
        $groupId = null;
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidateGroupIdIsWrong(): void
    {
        $groupId = 'wrong id';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateListOrdersIdIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = null;
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidateListOrdersIdIsWrong(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = 'wrong id';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = null;
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateProductIdIsNull(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = null;
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidateProductIdIsWrong(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'wrong id';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description modified';
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldValidateShopIdIsWrong(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = 'wrong id';
        $description = 'order description modified';
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailValidatingDescriptionIsTooLong(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = str_pad('', 501, 'p');
        $amount = 13.58;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailValidatingAmountIsNegative(): void
    {
        $groupId = '1d641e0d-da1f-4f8b-9d90-ab01a42ef620';
        $listOrdersId = '0e8a10dc-1a77-46b4-8340-bd6218cbec68';
        $orderId = 'cbf92e4f-8e96-4a43-90c3-2769a293facb';
        $productId = 'b87e6787-542f-4256-bb37-3e6d09a796e5';
        $shopId = '45abea59-3e11-4fbd-b0dc-cc0fc8608430';
        $description = 'order description';
        $amount = -1;

        $object = new OrderModifyInputDto(
            $this->userSession,
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['amount' => [VALIDATION_ERRORS::POSITIVE_OR_ZERO]], $return);
    }
}

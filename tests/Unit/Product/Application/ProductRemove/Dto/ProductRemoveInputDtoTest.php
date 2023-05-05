<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductRemove\Dto\ProductRemoveInputDto;

class ProductRemoveInputDtoTest extends TestCase
{
    private const GROUP_ID = '82281bf4-e6b6-4488-b907-fb1fd81ad509';
    private const PRODUCT_ID = '671bce77-d29d-46b9-8522-832527f496fa';
    private const SHOP_ID = 'de2cd24f-aaa6-46cd-9e60-875332f30d91';

    private ProductRemoveInputDto $object;
    private ValidationInterface $validator;
    private MockObject|UserShared $userShared;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userShared = $this->createMock(UserShared::class);
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $groupId = self::GROUP_ID;
        $productId = self::PRODUCT_ID;
        $shopId = self::SHOP_ID;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $productId = self::PRODUCT_ID;
        $shopId = self::SHOP_ID;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'wrong group id';
        $productId = self::PRODUCT_ID;
        $shopId = self::SHOP_ID;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $productId = null;
        $shopId = self::SHOP_ID;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $productId = 'product id wrong';
        $shopId = self::SHOP_ID;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $productId = self::PRODUCT_ID;
        $shopId = null;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $productId = self::PRODUCT_ID;
        $shopId = 'wrong shop id';
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdProductShopIdAreNull(): void
    {
        $groupId = null;
        $productId = null;
        $shopId = null;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
            $return);
    }
}

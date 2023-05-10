<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopRemove\Dto\ShopRemoveInputDto;

class ShopRemoveInputDtoTest extends TestCase
{
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';

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
        $shopId = self::SHOP_ID;
        $productId = self::PRODUCT_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $shopId = self::SHOP_ID;
        $productId = self::PRODUCT_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'wrong group id';
        $shopId = self::SHOP_ID;
        $productId = self::PRODUCT_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = null;
        $productId = self::PRODUCT_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = 'shop id wrong';
        $productId = self::PRODUCT_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = self::SHOP_ID;
        $productId = null;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = self::SHOP_ID;
        $productId = 'wrong shop id';
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdShopShopIdAreNull(): void
    {
        $groupId = null;
        $shopId = null;
        $productId = null;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId, $productId);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
            $return);
    }
}

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
    private const string GROUP_ID = '82281bf4-e6b6-4488-b907-fb1fd81ad509';
    private const string PRODUCT_ID = '671bce77-d29d-46b9-8522-832527f496fa';
    private const string SHOP_ID = 'de2cd24f-aaa6-46cd-9e60-875332f30d91';

    private ValidationInterface $validator;
    private MockObject|UserShared $userShared;

    #[\Override]
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
        $productsId = [self::PRODUCT_ID];
        $shopsId = [self::SHOP_ID];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateManyProductsAndShopIds(): void
    {
        $groupId = self::GROUP_ID;
        $productsId = [
            self::PRODUCT_ID,
            self::PRODUCT_ID,
        ];
        $shopsId = [
            self::SHOP_ID,
            self::SHOP_ID,
        ];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopsIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $productsId = [self::PRODUCT_ID];
        $shopsId = null;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $productsId = [self::PRODUCT_ID];
        $shopsId = [self::SHOP_ID];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'wrong group id';
        $productsId = [self::PRODUCT_ID];
        $shopsId = [self::SHOP_ID];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $productsId = null;
        $shopsId = [self::SHOP_ID];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $productsId = ['product id wrong'];
        $shopsId = [self::SHOP_ID];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $productsId = [self::PRODUCT_ID];
        $shopsId = ['wrong shop id'];
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdProductIdAreNull(): void
    {
        $groupId = null;
        $productsId = null;
        $shopsId = null;
        $object = new ProductRemoveInputDto($this->userShared, $groupId, $productsId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'products_id_empty' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return);
    }
}

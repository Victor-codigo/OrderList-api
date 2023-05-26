<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductSetShopPrice;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductSetShopPrice\Dto\ProductSetShopPriceInputDto;

class ProductSetShopPriceInputDtoTest extends TestCase
{
    private const PRODUCT_ID = 'c4e3c6c4-bcb4-46ec-ae00-71858ba6d46d';
    private const SHOP_ID = '7ba97dfe-ed91-4795-849e-7c07a700cd98';
    private const GROUP_ID = 'c9466728-e96c-4a68-8e33-479d524cf81b';

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
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, self::SHOP_ID, self::GROUP_ID, 0);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, null, self::SHOP_ID, self::GROUP_ID, 0);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, 'wrong id', self::SHOP_ID, self::GROUP_ID, 0);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, null, self::GROUP_ID, 0);
        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, 'wrong id', self::GROUP_ID, 0);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, self::SHOP_ID, null, 0);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, self::SHOP_ID, 'wrong id', 0);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailPriceIsNegative(): void
    {
        $object = new ProductSetShopPriceInputDto($this->userSession, self::PRODUCT_ID, self::SHOP_ID, self::GROUP_ID, -1);

        $return = $object->validate($this->validator);

        $this->assertEquals(['price' => [VALIDATION_ERRORS::POSITIVE_OR_ZERO]], $return);
    }
}

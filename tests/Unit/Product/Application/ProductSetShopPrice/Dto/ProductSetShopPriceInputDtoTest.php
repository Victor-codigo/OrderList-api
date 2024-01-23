<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductSetShopPrice\Dto;

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
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'shops_prices_not_equal' => [VALIDATION_ERRORS::NOT_EQUAL_TO],
                'products_id' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            ['id wrong', self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            null,
            [1, 2]
        );
        $return = $object->validate($this->validator);

        $this->assertEquals([
                'shops_prices_not_equal' => [VALIDATION_ERRORS::NOT_EQUAL_TO],
                'shops_id' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, 'wrong id'],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            null,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            'wrong id',
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailPriceIsNull(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'shops_prices_not_equal' => [VALIDATION_ERRORS::NOT_EQUAL_TO],
                'prices' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailPriceIsNegative(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [self::SHOP_ID, self::SHOP_ID],
            [-1, null]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['prices' => [
                [VALIDATION_ERRORS::POSITIVE_OR_ZERO],
                [VALIDATION_ERRORS::NOT_NULL],
            ]],
            $return
        );
    }

    /** @test */
    public function itShouldFailProductsIsShopsIsAndPricesAreNull(): void
    {
        $object = new ProductSetShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            null,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'products_id' => [VALIDATION_ERRORS::NOT_BLANK],
                'shops_id' => [VALIDATION_ERRORS::NOT_BLANK],
                'prices' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }
}

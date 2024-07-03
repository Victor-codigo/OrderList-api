<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\SetProductShopPrice\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\SetProductShopPrice\Dto\SetProductShopPriceInputDto;

class SetProductShopPriceInputDtoTest extends TestCase
{
    private const string PRODUCT_ID = 'c4e3c6c4-bcb4-46ec-ae00-71858ba6d46d';
    private const string SHOP_ID = '7ba97dfe-ed91-4795-849e-7c07a700cd98';
    private const string GROUP_ID = 'c9466728-e96c-4a68-8e33-479d524cf81b';

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateProductId(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopId(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            self::SHOP_ID,
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductsOrShopsIdPricesAndUnitsEmpty(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [],
            [],
            []
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailProductIdAndShopIdAreNull(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id_and_shop_id' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            'id wrong',
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            'wrong id',
            [self::PRODUCT_ID, self::PRODUCT_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            null,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            'wrong id',
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailPricesIsNull(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            null,
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'products_or_shops_prices_units_not_equals' => [VALIDATION_ERRORS::NOT_EQUAL_TO],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailUnitsIsNull(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, 2],
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'products_or_shops_prices_units_not_equals' => [VALIDATION_ERRORS::NOT_EQUAL_TO],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailPricesIsNegative(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [-1, null],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['prices' => [[VALIDATION_ERRORS::POSITIVE_OR_ZERO]]], $return);
    }

    /** @test */
    public function itShouldFailUnitsIsNotValid(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, self::SHOP_ID],
            [1, null],
            [UNIT_MEASURE_TYPE::UNITS->value, 'wrong unit']
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['units' => [[VALIDATION_ERRORS::CHOICE_NOT_SUCH]]], $return);
    }

    /** @test */
    public function itShouldFailProductsOrShopsIdIrWrong(): void
    {
        $object = new SetProductShopPriceInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::PRODUCT_ID,
            null,
            [self::SHOP_ID, 'wrong id'],
            [null, null],
            [UNIT_MEASURE_TYPE::UNITS->value, UNIT_MEASURE_TYPE::KG->value]
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_or_shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }
}

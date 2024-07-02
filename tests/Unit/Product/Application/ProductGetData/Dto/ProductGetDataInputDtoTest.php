<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductGetData\Dto\ProductGetDataInputDto;

class ProductGetDataInputDtoTest extends TestCase
{
    private const string GROUP_ID = 'd511f745-22ef-4de8-933f-81e1ffcda810';
    private const array PRODUCTS_ID = [
        'd511f745-22ef-4de8-933f-81e1ffcda810',
        'eb221850-c5d1-4cb2-939d-d89d2f732db1',
    ];
    private const array SHOPS_ID = [
        'd511f745-22ef-4de8-933f-81e1ffcda810',
        'f0872de3-bc35-4572-b69f-2c9bfd28f220',
    ];

    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductsIdIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            null,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopsIdIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            null,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductNameIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductNameFilterIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            null,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopNameFilterIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            null,
            null,
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            null,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            'group id wrong',
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductsIdAreWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            ['product1 id wrong', 'product2 id wrong'],
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopsIdAreWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            ['shop1 id wrong', 'shop2 id wrong'],
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailProductNameIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name wrong-',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailProductNameFilterTypeIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            null,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductNameFilterTypeIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            'filter wrong type',
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductNameFilterValueIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailProductNameFilterValueIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name wrong-',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name_filter_value' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterTypeIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            'filter wrong type',
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterTypeIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            'filter wrong type',
            'shop name',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterValueIsNull(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            null,
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterValueIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name wrong-',
            true,
            1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name_filter_value' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            -1,
            10,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new ProductGetDataInputDto(
            self::GROUP_ID,
            self::PRODUCTS_ID,
            self::SHOPS_ID,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'product name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'shop name',
            true,
            1,
            -1,
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}

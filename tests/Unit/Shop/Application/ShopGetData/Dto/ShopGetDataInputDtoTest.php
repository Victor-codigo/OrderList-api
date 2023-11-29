<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopGetData\Dto\ShopGetDataInputDto;
use Shop\Application\ShopGetData\SHOP_GET_DATA_FILTER;

class ShopGetDataInputDtoTest extends TestCase
{
    private const GROUP_ID = 'd511f745-22ef-4de8-933f-81e1ffcda810';
    private const PRODUCTS_ID = [
        'd511f745-22ef-4de8-933f-81e1ffcda810',
        'eb221850-c5d1-4cb2-939d-d89d2f732db1',
    ];
    private const SHOPS_ID = [
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
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopsIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            null,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductsIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            null,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateNoFilter(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            null,
            null,
            null,
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            null,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            'wrong id',
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopsIdAreWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            [
                'wrong id 1',
                'wrong id 2',
            ],
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailProductsIdAreWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            [
                'wrong id 1',
                'wrong id 2',
            ],
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopFilterNameIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            null,
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_name' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH]], $return);
    }

    /** @test */
    public function itShouldFailShopFilterNameIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            'wong name',
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            null,
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_name' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterTypeIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            null,
            'shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterTypeIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            'wrong filter',
            'Shop',
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopNameFilterValueIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            null,
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopFilterValueIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            str_pad('', 51, 'p'),
            'shop name',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_filter_value' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailShopValueIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name-',
            true,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            null,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            -1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
            SHOP_GET_DATA_FILTER::SHOP_NAME->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'Shop',
            'shop name',
            true,
            1,
            -1
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailAllInputsAreWrong(): void
    {
        $object = new ShopGetDataInputDto(
            'wrong id',
            [
                'wrong id 1',
                'wrong id 2',
            ],
            [
                'wrong id 1',
                'wrong id 2',
            ],
            'wrong name',
            null,
            'Shop -',
            'shop name-',
            true,
            -1,
            101
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
                'shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'shop_filter_name' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH],
                'shop_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'shop_filter_value' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
                'shop_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
                'page' => [VALIDATION_ERRORS::GREATER_THAN],
                'page_items' => [VALIDATION_ERRORS::LESS_THAN_OR_EQUAL],
            ],
            $return
        );
    }
}

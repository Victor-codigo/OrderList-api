<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopGetData\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopGetData\Dto\ShopGetDataInputDto;

class ShopGetDataInputDtoTest extends TestCase
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

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldValidateShopsIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            null,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldValidateProductsIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            null,
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

    #[Test]
    public function itShouldValidateNoFilter(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            null,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            'wrong id',
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailShopsIdAreWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            [
                'wrong id 1',
                'wrong id 2',
            ],
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailProductsIdAreWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            [
                'wrong id 1',
                'wrong id 2',
            ],
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

    #[Test]
    public function itShouldFailShopNameFilterTypeIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailShopNameFilterTypeIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailShopNameFilterValueIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailShopFilterValueIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailShopValueIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailPageIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailPageIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new ShopGetDataInputDto(
            self::GROUP_ID,
            self::SHOPS_ID,
            self::PRODUCTS_ID,
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

    #[Test]
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

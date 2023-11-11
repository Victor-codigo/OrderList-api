<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductGetData\Dto\ProductGetDataInputDto;

class ProductGetDataInputDtoTest extends TestCase
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
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductsIdIsNull(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(self::GROUP_ID, null, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopsIdIsNull(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, null, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductNameStartsWithIsNull(): void
    {
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, self::SHOPS_ID, null, $productName);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductNameIsNull(): void
    {
        $productName = null;
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, self::SHOPS_ID, null, $productName);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(null, self::PRODUCTS_ID, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $object = new ProductGetDataInputDto('wrong id', self::PRODUCTS_ID, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductsIdAreWrong(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $productsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ProductGetDataInputDto(self::GROUP_ID, $productsId, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopsIdAreWrong(): void
    {
        $productNameStartsWith = 'jp';
        $productName = 'shop name';
        $shopsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, $shopsId, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailProductNameStartsWithIsWrong(): void
    {
        $productNameStartsWith = str_pad('', 51, 'p');
        $productName = 'shop name';
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailProductNameIsWrong(): void
    {
        $productNameStartsWith = 'Ju';
        $productName = 'shop name-';
        $object = new ProductGetDataInputDto(self::GROUP_ID, self::PRODUCTS_ID, self::SHOPS_ID, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals(['product_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailAllInputsAreWrong(): void
    {
        $productNameStartsWith = str_pad('', 51, 'p');
        $productName = 'shop name-';
        $groupId = 'wrong id';
        $productsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $shopsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ProductGetDataInputDto($groupId, $productsId, $shopsId, $productNameStartsWith, $productName);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
                'products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'product_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG],
                'product_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
            ],
            $return
        );
    }
}
